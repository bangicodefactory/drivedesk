<?php

namespace App\Http\Controllers;

use App\Models\ExpenseType;
use Illuminate\Http\Request;

class ExpenseTypeController extends Controller
{

    public function index()
    {
        if (\Auth::user()->can('manage expense type')) {
            $types = ExpenseType::where('parent_id', parentId())->get();
            return view('expense_type.index', compact('types'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function create()
    {
        return view('expense_type.create');
    }


    public function store(Request $request)
    {
        if (\Auth::user()->can('create expense type')) {
            $validator = \Validator::make(
                $request->all(), [
                    'title' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            $expenseType = new ExpenseType();
            $expenseType->title = $request->title;
            $expenseType->parent_id = parentId();
            $expenseType->save();
            return redirect()->route('expense-type.index')->with('success', __('Expense type successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function show(ExpenseType $expenseType)
    {
        //
    }


    public function edit(ExpenseType $expenseType)
    {
        return view('expense_type.edit',compact('expenseType'));
    }


    public function update(Request $request, ExpenseType $expenseType)
    {
        if (\Auth::user()->can('edit expense type')) {
            $validator = \Validator::make(
                $request->all(), [
                    'title' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            $expenseType->title = $request->title;
            $expenseType->save();
            return redirect()->route('expense-type.index')->with('success', __('Expense type successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function destroy(ExpenseType $expenseType)
    {
        if (\Auth::user()->can('delete expense type')) {
            $expenseType->delete();
            return redirect()->route('expense-type.index')->with('success', __('Expense type successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
