<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NotificationController extends Controller
{
    /**
     * Normalize the static module catalogue for the SPA. Tolerates the legacy
     * 'templete' key (only 'user_create' uses it; the rest use 'template').
     */
    private function moduleCatalogue(): array
    {
        return collect(Notification::$modules)->map(function ($module, $key) {
            return [
                'key'        => $key,
                'name'       => $module['name'],
                'subject'    => $module['subject'] ?? '',
                'template'   => $module['templete'] ?? $module['template'] ?? '',
                'short_code' => $module['short_code'] ?? [],
            ];
        })->values()->all();
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (\Auth::user()->can('manage notification')) {
            $notifications = Notification::where('parent_id', parentId())->get();
            return Inertia::render('Notification/Index', [
                'notifications' => $notifications->map(fn ($item) => [
                    'id'            => $item->id,
                    'name'          => $item->name,
                    'subject'       => $item->subject,
                    'enabled_email' => (int) $item->enabled_email,
                ])->values(),
            ]);
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return Inertia::render('Notification/Create', [
            'modules' => $this->moduleCatalogue(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (\Auth::user()->can('create notification')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'module' => 'required',
                    'subject' => 'required',
                    'message' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $exist = Notification::where('parent_id', parentId())->where('module', $request->module)->first();
            if (empty($exist)) {
                $notification = new Notification();
                $notification->module = $request->module;
                $notification->subject = $request->subject;
                $notification->message = $request->message;
                $notification->enabled_email = isset($request->enabled_email) ? 1 : 0;
                $notification->parent_id = parentId();
                $notification->save();

                return redirect()->route('notification.index')->with('success', __('Notification successfully created.'));
            } else {
                return redirect()->back()->with('error', __('Notification already exist'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Notification  $notification
     * @return \Illuminate\Http\Response
     */
    public function show(Notification $notification)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Notification  $notification
     * @return \Illuminate\Http\Response
     */
    public function edit(Notification $notification)
    {
        $module = Notification::$modules[$notification->module] ?? null;

        return Inertia::render('Notification/Edit', [
            'notification' => [
                'id'            => $notification->id,
                'module'        => $notification->module,
                'name'          => $notification->name,
                'subject'       => $notification->subject,
                'message'       => $notification->message,
                'enabled_email' => (int) $notification->enabled_email,
            ],
            'shortCodes' => $module['short_code'] ?? [],
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Notification  $notification
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Notification $notification)
    {
        if (\Auth::user()->can('edit notification')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'subject' => 'required',
                    'message' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $notification->module = $notification->module;
            $notification->subject = $request->subject;
            $notification->message = $request->message;
            $notification->enabled_email = $request->enabled_email;
            $notification->save();

            return redirect()->route('notification.index')->with('success', __('Notification successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Notification  $notification
     * @return \Illuminate\Http\Response
     */
    public function destroy(Notification $notification)
    {
        if (\Auth::user()->can('delete notification')) {
            $notification->delete();
            return redirect()->back()->with('success', __('Notification successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
