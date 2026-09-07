<?php

namespace App\Http\Controllers;

use App\Models\LoggedHistory;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Role names that must never become a user's `type` (BAN-307).
     *
     * store() and update() both set `type` verbatim from the chosen role's
     * name, and role names are user-supplied -- RoleController validates
     * uniqueness, not content. 'owner' breaks the one-owner-per-deployment
     * invariant. 'super admin' is worse: BelongsToTenant::tenantScopeApplies()
     * returns false for that type, so a user carrying it drops the tenant scope
     * on every model in the app and flips every `type == 'super admin'` branch
     * in the controllers.
     */
    private const RESERVED_TYPES = ['owner', 'super admin'];


    public function index()
    {
        if (\Auth::user()->can('manage user')) {
            if (\Auth::user()->type == 'super admin') {
                $users = User::where('parent_id', parentId())->where('type', 'owner')->get();
            } else {
                $users = User::where('parent_id', '=', parentId())->whereNotIn('type', ['driver'])->get();
            }

            return Inertia::render('Users/Index', [
                'users' => $users->map(fn ($u) => [
                    'id'           => $u->id,
                    'name'         => $u->name,
                    'email'        => $u->email,
                    'type'         => $u->type,
                    'is_active'    => (bool) $u->is_active,
                    'company_name' => $u->company_name,
                    'created_at'   => optional($u->created_at)->toDateString(),
                ])->values()->all(),
            ]);
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function create()
    {
        $userRoles = Role::where('parent_id', parentId())->whereNotIn('name', ['driver'])->get()->pluck('name', 'id');

        return Inertia::render('Users/Create', [
            'userRoles' => $userRoles->map(fn ($name, $id) => ['id' => $id, 'name' => $name])->values()->all(),
        ]);
    }


    public function store(Request $request)
    {
        if (\Auth::user()->can('create user')) {
            if (\Auth::user()->type == 'super admin') {
                $validator = \Validator::make(
                    $request->all(), [
                        'name' => 'required',
                        'email' => 'required|email|unique:users',
                        'password' => 'required|min:6',
                    ]
                );
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                // BAN-307: one owner per deployment. Nothing enforced this, so
                // a support login could add a second tenant inside a customer's
                // database -- invisible to the customer, counted in their totals.
                if (User::ownerExists()) {
                    return redirect()->back()->with('error', __('This deployment already has an owner.'));
                }

                $user = new User();
                $user->name = $request->name;
                $user->email = $request->email;
                $user->password = \Hash::make($request->password);
                $user->phone_number = !empty($request->phone_number) ? $request->phone_number : null;
                $user->type = 'owner';
                $user->lang = 'english';
                $user->parent_id = parentId();
                $user->save();
                $userRole = Role::findByName('owner');
                $user->assignRole($userRole);
                defaultDriverCreate($user->id);
                defultTemplate($user->id);

                $module = 'owner_create';
                $setting = settings();
                $errorMessage = '';
                if (!empty($user)) {
                    $data['subject'] = 'New User Created';
                    $data['module'] = $module;
                    $data['password'] = $request->password;
                    $data['name'] = $request->name;
                    $data['email'] = $request->email;
                    $data['url'] = env('APP_URL');
                    $data['logo'] = $setting['company_logo'];
                    $to = $user->email;
                    $response = commonEmailSend($to, $data);
                    if ($response['status'] == 'error') {
                        $errorMessage=$response['message'];
                    }
                }


                return redirect()->route('users.index')->with('success', __('User successfully created.') . '</br>' . $errorMessage);
            } else {

                $validator = \Validator::make(
                    $request->all(), [
                        'name' => 'required',
                        'email' => 'required|email|unique:users',
                        'password' => 'required|min:6',
                        'role' => 'required',
                    ]
                );
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();
                    return redirect()->back()->with('error', $messages->first());
                }

                // BAN-307: Role::findById() was unscoped while create() only
                // offers this tenant's roles, so a crafted role id set the new
                // user's type to anything -- including 'owner'. Resolve within
                // the tenant, exactly as the form was populated.
                $userRole = Role::where('parent_id', parentId())->find($request->role);
                if ($userRole === null || in_array($userRole->name, self::RESERVED_TYPES, true)) {
                    return redirect()->back()->with('error', __('Permission Denied.'));
                }

                $user = new User();
                $user->name = $request->name;
                $user->phone_number = !empty($request->phone_number) ? $request->phone_number : null;
                $user->email = $request->email;
                $user->password = \Hash::make($request->password);
                $user->type = $userRole->name;
                $user->profile = 'avatar.png';
                $user->lang = 'english';
                $user->parent_id = parentId();
                //add email verification
                $user->email_verified_at = now();
                $user->save();

                $user->assignRole($userRole);

                $module = 'user_create';
                $notification = Notification::where('parent_id', parentId())->where('module', $module)->first();
                $notification->password = $request->password;
                $setting = settings();
                $errorMessage = '';
                if (!empty($notification) && $notification->enabled_email == 1) {
                    $notification_responce = MessageReplace($notification, $user->id);
                    $data['subject'] = $notification_responce['subject'];
                    $data['message'] = $notification_responce['message'];
                    $data['module'] = $module;
                    $data['password'] = $request->password;
                    $data['logo'] = $setting['company_logo'];
                    $to = $user->email;

                    $response = commonEmailSend($to, $data);
                    if ($response['status'] == 'error') {
                        $errorMessage=$response['message'];
                    }
                }
                return redirect()->route('users.index')->with('success', __('User successfully created.') . '</br>' . $errorMessage);
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function show($id)
    {
        //
    }


    public function edit($id)
    {
        $user = User::findOrFail($id);
        $userRoles = Role::where('parent_id', '=', parentId())->whereNotIn('name', ['driver'])->get()->pluck('name', 'id');

        return Inertia::render('Users/Edit', [
            'user' => [
                'id'           => $user->id,
                'name'         => $user->name,
                'email'        => $user->email,
                'type'         => $user->type,
                'is_active'    => (bool) $user->is_active,
                'company_name' => $user->company_name,
            ],
            'userRoles' => $userRoles->map(fn ($name, $id) => ['id' => $id, 'name' => $name])->values()->all(),
        ]);
    }



    public function update(Request $request, $id)
    {
        if (\Auth::user()->can('edit user')) {
            if (\Auth::user()->type == 'super admin') {
                $user = User::findOrFail($id);

                $validator = \Validator::make(
                    $request->all(), [
                        'name' => 'required',
                        'email' => 'required|email|unique:users,email,' . $id,
                    ]
                );
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                // BAN-307: an allowlist, not a denylist. $request->all() filled
                // every fillable column: `type` and `parent_id` (promote a user
                // to 'owner', or move them to another tenant) and also
                // `password`, which User has no `hashed` cast for -- a crafted
                // request wrote it to the column in plaintext and locked the
                // account out. These are the fields Users/Edit.jsx actually
                // posts, plus the profile fields the form may carry.
                $userData = $request->only([
                    'name', 'email', 'phone_number', 'is_active', 'company_name', 'city',
                ]);
                $user->fill($userData)->save();

                return redirect()->route('users.index')->with('success', 'User successfully updated.');
            } else {


                $validator = \Validator::make(
                    $request->all(), [
                        'name' => 'required',
                        'email' => 'required|email|unique:users,email,' . $id,
                        'role' => 'required',
                    ]
                );
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                // BAN-307: both lookups were unscoped, and `type` is set from
                // the role's name below. Role::findById() reaches the seeded
                // `owner` role (parent_id = the super admin's id, so edit()'s
                // picker never offers it), so any holder of `edit user` could
                // PUT role=<owner role id> and promote a user -- themselves
                // included -- to a second owner, with that role's permissions
                // synced on. User::findOrFail() reached rows outside the
                // caller's tenant, matching neither index() nor edit()'s picker.
                $userRole = Role::where('parent_id', parentId())->find($request->role);
                if ($userRole === null || in_array($userRole->name, self::RESERVED_TYPES, true)) {
                    return redirect()->back()->with('error', __('Permission Denied.'));
                }

                $user = User::where('parent_id', parentId())->findOrFail($id);
                $user->name = $request->name;
                $user->email = $request->email;
                $user->phone_number = !empty($request->phone_number) ? $request->phone_number : null;
                $user->type = $userRole->name;
                $user->save();
                $user->roles()->sync($userRole);
                return redirect()->route('users.index')->with('success', 'User successfully updated.');
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function destroy($id)
    {

        if (\Auth::user()->can('delete user') ) {
            $user = User::find($id);
            $user->delete();

            return redirect()->route('users.index')->with('success', __('User successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function loggedHistory()
    {
        if (\Auth::user()->can('manage logged history')) {
            $histories = LoggedHistory::where('parent_id', parentId())->get();
            return view('logged_history.index', compact('histories'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function loggedHistoryShow($id)
    {
        if (\Auth::user()->can('manage logged history')) {
            $histories = LoggedHistory::find($id);
            return view('logged_history.show', compact('histories'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function loggedHistoryDestroy($id)
    {
        if (\Auth::user()->can('delete logged history')) {
            $histories = LoggedHistory::find($id);
            $histories->delete();
            return redirect()->back()->with('success', 'Logged history succefully deleted.');
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


}
