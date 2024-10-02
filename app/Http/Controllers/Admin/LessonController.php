<?php

namespace App\Http\Controllers\Admin;

use App\Models\Lesson;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class LessonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //get lessons
        $lessons = Lesson::when(request()->q, function($lessons) {
            $lessons = $lessons->where('title', 'like', '%'. request()->q . '%');
        })->latest()->paginate(5);

        //append query string to pagination links
        $lessons->appends(['q' => request()->q]);

        //render with inertia
        return inertia('Admin/Lessons/Index', [
            'lessons' => $lessons,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return inertia('Admin/Lessons/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //validate request
        $request->validate([
            'title' => 'required|string|unique:lessons',
        ]);

        //create lesson
        Lesson::create([
            'title' => $request->title,
        ]);

        //redirect
        return redirect()->route('admin.lessons.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //get lesson
        $lesson = Lesson::findOrFail($id);

        //render with inertia
        return inertia('Admin/Lessons/Edit', [
            'lesson' => $lesson,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Lesson $lesson)
    {
        //validate request
        $request->validate([
            'title' => 'required|string|unique:lessons,title,'.$lesson->id,
        ]);

        //update lesson
        $lesson->update([
            'title' => $request->title,
        ]);

        //redirect
        return redirect()->route('admin.lessons.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //get lesson
        $lesson = Lesson::findOrFail($id);

        //delete lesson
        $lesson->delete();

        //redirect
        return redirect()->route('admin.lessons.index');
    }
}
