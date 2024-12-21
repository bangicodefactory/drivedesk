<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseType;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{

    public function index()
    {
        if (\Auth::user()->can('manage expense')) {
            $expenses = Expense::where('parent_id', '=', parentId())->get();
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
        return view('expense.index', compact('expenses'));
    }


    public function create()
    {
        $vehicles = Vehicle::where('parent_id', parentId())->get()->pluck('name', 'id');
        $vehicles->prepend(__('Select Vehicle'),'');

        $types = ExpenseType::where('parent_id', parentId())->get()->pluck('title', 'id');
        $types->prepend(__('Select Type'),'');
        return view('expense.create', compact('vehicles','types'));
    }


    public function store(Request $request)
    {
        if (\Auth::user()->can('create expense')) {
            $validator = \Validator::make(
                $request->all(), [
                    'title' => 'required',
                    'type' => 'required',
                    'date' => 'required',
                    'amount' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $expense = new Expense();
            $expense->title = $request->title;
            $expense->type = $request->type;
            $expense->vehicle = !empty($request->vehicle)?$request->vehicle:0;
            $expense->date = $request->date;
            $expense->amount = $request->amount;
            $expense->notes = $request->notes;
            $expense->parent_id = parentId();

            if (!empty($request->receipt)) {

                $expenseFilenameWithExt = $request->file('receipt')->getClientOriginalName();
                $expenseFilename = pathinfo($expenseFilenameWithExt, PATHINFO_FILENAME);
                $expenseExtension = $request->file('receipt')->getClientOriginalExtension();
                $expenseFileName = $expenseFilename . '_' . time() . '.' . $expenseExtension;

                $dir = storage_path('upload/expense');
                $image_path = $dir . $expenseFilenameWithExt;

                if (!file_exists($dir)) {
                    mkdir($dir, 0777, true);
                }
                $request->file('receipt')->storeAs('upload/expense/', $expenseFileName);
                $expense->receipt = $expenseFileName;
            }
            $expense->save();

            return redirect()->route('expense.index')->with('success', __('Expense successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function show(Expense $expense)
    {
        //
    }


    public function edit(Expense $expense)
    {
        $vehicles = Vehicle::where('parent_id', parentId())->get()->pluck('name', 'id');
        $vehicles->prepend(__('Select Vehicle'),'');

        $types = ExpenseType::where('parent_id', parentId())->get()->pluck('title', 'id');
        $types->prepend(__('Select Type'),'');
        return view('expense.edit', compact('vehicles','expense','types'));
    }


    public function update(Request $request, Expense $expense)
    {
        if (\Auth::user()->can('edit expense')) {
            $validator = \Validator::make(
                $request->all(), [
                    'title' => 'required',
                    'type' => 'required',
                    'date' => 'required',
                    'amount' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }
            $expense->title = $request->title;
            $expense->type = $request->type;
            $expense->vehicle = !empty($request->vehicle)?$request->vehicle:0;
            $expense->date = $request->date;
            $expense->amount = $request->amount;
            $expense->notes = $request->notes;
            if (!empty($request->receipt)) {
                $expenseFilenameWithExt = $request->file('receipt')->getClientOriginalName();
                $expenseFilename = pathinfo($expenseFilenameWithExt, PATHINFO_FILENAME);
                $expenseExtension = $request->file('receipt')->getClientOriginalExtension();
                $expenseFileName = $expenseFilename . '_' . time() . '.' . $expenseExtension;
                $dir = storage_path('upload/expense');
                $image_path = $dir . $expenseFilenameWithExt;
                if (!file_exists($dir)) {
                    mkdir($dir, 0777, true);
                }
                $request->file('receipt')->storeAs('upload/expense/', $expenseFileName);
                $expense->receipt = $expenseFileName;
            }
            $expense->save();

            return redirect()->route('expense.index')->with('success', __('Expense successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function destroy(Expense $expense)
    {
        if (\Auth::user()->can('delete expense')) {
            $expense->delete();
            return redirect()->route('expense.index')->with('success', __('Expense successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
