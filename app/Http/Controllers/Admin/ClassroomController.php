<?php

namespace App\Http\Controllers\Admin;

use App\Models\Classroom;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ClassroomController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //get classrooms
        $classrooms = Classroom::when(request()->q, function($classrooms) {
            $classrooms = $classrooms->where('title', 'like', '%'. request()->q . '%');
        })->latest()->paginate(5);

        //append query string to pagination links
        $classrooms->appends(['q' => request()->q]);

        //render with inertia
        return inertia('Admin/Classrooms/Index', [
            'classrooms' => $classrooms,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //render with inertia
        return inertia('Admin/Classrooms/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //validate request
        $request->validate([
            'title' => 'required|string|unique:classrooms'
        ]);

        //create classroom
        Classroom::create([
            'title' => $request->title,
        ]);

        //redirect
        return redirect()->route('admin.classrooms.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //get classroom
        $classroom = Classroom::findOrFail($id);

        //render with inertia
        return inertia('Admin/Classrooms/Edit', [
            'classroom' => $classroom,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Classroom $classroom)
    {
        //validate request
        $request->validate([
            'title' => 'required|string|unique:classrooms,title,'.$classroom->id,
        ]);

        //update classroom
        $classroom->update([
            'title' => $request->title,
        ]);

        //redirect
        return redirect()->route('admin.classrooms.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //get classroom
        $classroom = Classroom::findOrFail($id);

        //delete classroom
        $classroom->delete();

        //redirect
        return redirect()->route('admin.classrooms.index');
    }
}
