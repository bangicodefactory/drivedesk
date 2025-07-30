<?php

namespace App\Http\Controllers;

use App\Models\Option;
use Illuminate\Http\Request;

class OptionController extends Controller
{

    public function index()
    {
        if (\Auth::user()->can('manage options')) {
            $options = Option::where('parent_id', parentId())->get();
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
        return view('option.index', compact('options'));
    }


    public function create()
    {
        return view('option.create');
    }


    public function store(Request $request)
    {
        if (\Auth::user()->can('create options')) {
            $validator = \Validator::make(
                $request->all(), [
                    'name' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }
            $option = new Option();
            $option->name = $request->name;
            $option->parent_id = parentId();
            $option->save();
            return redirect()->route('option.index')->with('success', __('Option successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function show(Option $option)
    {
        //
    }


    public function edit(Option $option)
    {
        return view('option.edit',compact('option'));
    }


    public function update(Request $request, Option $option)
    {
        if (\Auth::user()->can('edit options')) {
            $validator = \Validator::make(
                $request->all(), [
                    'name' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }
            $option->name = $request->name;
            $option->save();
            return redirect()->route('option.index')->with('success', __('Option successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function destroy(Option $option)
    {
        if (\Auth::user()->can('delete options') ) {
            $option->delete();
            return redirect()->route('option.index')->with('success', __('Option successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
