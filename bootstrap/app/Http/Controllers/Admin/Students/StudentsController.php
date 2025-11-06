<?php

namespace App\Http\Controllers\Admin\Students;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class StudentsController extends Controller
{
    public function index()
    {
        $students = User::where('user_type', User::USER_TYPE['student'])->get();
        return view('admin.students.index', compact('students'));
    }

    public function create()
    {
        return view('admin.students.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_name' => 'required',
            'whatsapp_number' => 'required',
            'currency' => 'required',
            'hour_price' => 'required',
        ]);
        $data['user_type'] = User::USER_TYPE['student'];
        $data['password'] = '0000';
        $user = User::create($data);
        return redirect()->route('students.index');
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'user_name' => 'required',
            'whatsapp_number' => 'required',
            'currency' => 'required',
            'hour_price' => 'required',
        ]);
        $user = User::find($id);
        $user->update($data);
        return redirect()->route('students.index');
    }

    public function delete($id)
    {
        $user = User::find($id);
        $user->delete();
        return redirect()->route('students.index');
    }
}
