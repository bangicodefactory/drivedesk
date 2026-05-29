<?php

namespace App\Http\Controllers;

use App\Models\LoggedHistory;
use App\Models\Notification;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{

    public function index()
    {
        if (\Auth::user()->can('manage user')) {
            if (\Auth::user()->type == 'super admin') {
                $users = User::where('parent_id', parentId())->where('type', 'owner')->get();
            } else {
                $users = User::where('parent_id', '=', parentId())->whereNotIn('type', ['driver'])->get();
            }

            if (config('app.inertia_enabled')) {
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
            }

            return view('user.index', compact('users'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function create()
    {
        $userRoles = Role::where('parent_id', parentId())->whereNotIn('name', ['driver'])->get()->pluck('name', 'id');

        if (config('app.inertia_enabled')) {
            return Inertia::render('Users/Create', [
                'userRoles' => $userRoles->map(fn ($name, $id) => ['id' => $id, 'name' => $name])->values()->all(),
            ]);
        }

        return view('user.create', compact('userRoles'));
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

                $user = new User();
                $user->name = $request->name;
                $user->email = $request->email;
                $user->password = \Hash::make($request->password);
                $user->phone_number = !empty($request->phone_number) ? $request->phone_number : null;
                $user->type = 'owner';
                $user->lang = 'english';
                $user->subscription = 1;
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

                $ids = parentId();
                $authUser = \App\Models\User::find($ids);
                $totalUser = $authUser->totalUser();
                $subscription = Subscription::find($authUser->subscription);
                if ($totalUser >= $subscription->user_limit && $subscription->user_limit != 0) {
                    return redirect()->back()->with('error', __('Your user limit is over, please upgrade your subscription.'));
                }
                $userRole = Role::findById($request->role);
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

        if (config('app.inertia_enabled')) {
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

        return view('user.edit', compact('user', 'userRoles'));
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

                $userData = $request->all();
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

                $userRole = Role::findById($request->role);
                $user = User::findOrFail($id);
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
        $ids = parentId();
        $authUser = \App\Models\User::find($ids);
        $subscription = \App\Models\Subscription::find($authUser->subscription);

        if (\Auth::user()->can('manage logged history') && $subscription->enabled_logged_history == 1) {
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
