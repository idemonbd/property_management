<?php

namespace App\Http\Controllers;

use App\Accountant;
use App\Loan;
use App\LoanReturn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoanReturnController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // return $request;
        $request->validate([
            'loan_id' => 'required',
            'amount' => 'required|integer|gt:0',
        ]);

        // check if request loan found or not
        if (!(Loan::find($request->loan_id))) {
            return redirect()->back()->with('info', 'Loan not found');
        }

        $return = new LoanReturn();

        // find or fail get loan
        $loan = Loan::findOrFail($request->loan_id);
        $return->remain = ($loan->return_amount) - ($loan->returns->sum('amount'));

        // check if loan completed
        if ($return->remain == 0) {
            return redirect()->back()->with('info', 'Loan fully returned');
        }

        // Check if user pay more then remaining amount
        if ($return->remain < $request->amount) {
            return redirect()->back()->with('error', 'You can\'t return more then remaining amount');
        }

        // set return counter
        $return->loancounter = $loan->id . '-' . ($loan->returns->count() + 1);

        // set remain
        if ($return->remain == $request->amount) {
            $return->remain = 0;
        } else {
            $return->remain = ($return->remain) - ($request->amount);
        }

        $return->loan_id = $request->loan_id;
        if (!($return->accountant_id = Accountant::active()->id)) {
            return redirect(route('accountant.index'))->with('info', 'Set an accountant first');
        }
        $return->entry_id = Auth::id();
        $return->amount = $request->amount;
        $return->description = $request->description;
        $return->created_at = $request->created_at;
        $return->save();

        return redirect()->back()->with('success', 'Successfull');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\LoanReturn  $return
     * @return \Illuminate\Http\Response
     */
    public function show(LoanReturn $return)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\LoanReturn  $return
     * @return \Illuminate\Http\Response
     */
    public function edit(LoanReturn $return)
    {
        return view('loan.return-edit',compact('return'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\LoanReturn  $return
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, LoanReturn $return)
    {
        // return $request;
        $request->validate([
            'amount' => 'required|integer|gt:0',
        ]);

        $return->amount = $request->amount;
        $return->description = $request->description;
        $return->created_at = $request->created_at;
        $return->save();

        return redirect(route('loan.index'))->with('success', 'Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\LoanReturn  $return
     * @return \Illuminate\Http\Response
     */
    public function destroy(LoanReturn $return)
    {
        //
    }
}
