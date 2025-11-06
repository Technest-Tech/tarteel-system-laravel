<?php

namespace App\Http\Controllers\Admin\Billings;

use App\Http\Controllers\Controller;
use App\Models\Billings;
use Illuminate\Http\Request;

class BillingsController extends Controller
{
    public function index($month)
    {
        $billings = \App\Models\Billings::where('is_paid',0)
            ->whereMonth('created_at',$month)
            ->selectRaw('student_id, sum(amount) as total_amount')
            ->groupBy('student_id')
            ->get();
        return view('admin.bilings.index',compact('billings','month'));
    }

    public function pay($student_id,$month,$amount,$currency)
    {
        $amount = round($amount, 2);
        $student = \App\Models\User::find($student_id);
        return view('pay.index',compact('student','amount','month','currency'));
    }

    public function success(Request $request,$month)
    {
        $userId = $request->input('student_id');
        Billings::where('student_id',$userId)->whereMonth('created_at',$month)->update(['is_paid'=>1]);
        return view('pay.success');
    }

    public function paidBillings($month)
    {
        $billings = \App\Models\Billings::where('is_paid',1)
            ->whereMonth('created_at',$month)
            ->selectRaw('student_id, sum(amount) as total_amount')
            ->groupBy('student_id')
            ->get();
        return view('admin.bilings.paid',compact('billings','month'));
    }

    public function salaries($month)
    {
        $salaries = \App\Models\Lessons::whereMonth('created_at',$month)
            ->selectRaw('teacher_id, sum(lesson_duration) as total_hours')
            ->groupBy('teacher_id')
            ->get();
        return view('admin.salaries.index',compact('salaries','month'));
    }

    public function salariesAmount($month,Request $request)
    {
        $salaries = \App\Models\Lessons::whereMonth('created_at',$month)
            ->selectRaw('teacher_id, sum(lesson_duration) as total_hours')
            ->groupBy('teacher_id')
            ->get();
        $amount = $request->input('amount');
        return view('admin.salaries.index',compact('salaries','month','amount'));
    }

}
