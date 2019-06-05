<?php

namespace App\Http\Controllers;

use App\courseFeedback;
use App\courses;
use App\programmingLanguages;
use App\userCourseUnlocks;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Storage;

class CoursesController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin')->except(['index', 'show', 'generateCertificate', 'completed', 'feedback']);
        $this->middleware('auth')->only(['show', 'generateCertificate', 'completed', 'feedback']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $courses = courses::all();
        return view('courses.index', [
            'courses' => $courses
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $languages = programmingLanguages::all();

        return view('courses.create', [
            'languages' => $languages
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:300'],
            'duration' => ['required', 'string', 'max:11'],
            'difficulty' => ['required', 'numeric', 'between:0,3'],
            'price' => ['required', 'numeric', 'between:0,1000'],
            'programming_language_id' => ['required', 'exists:programming_languages,id'],
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048']
        ]);

        $validated['image'] = $request->file('image')->store('courseImages', 'public');

        courses::create($validated);

        return redirect(route('courses.index'));
    }

    /**
     * Display the specified resource.
     *
     * @param \App\courses $course
     * @return \Illuminate\Http\Response
     */
    public function show(courses $course)
    {
        if (Auth()->user()->can('view', $course)) {
            return view('courses.show', [
                'course' => $course
            ]);
        } else {
            if (Auth()->user()->PayCredits($course->price)) {
                userCourseUnlocks::create([
                    'user_id' => Auth()->user()->id,
                    'course_id' => $course->id,
                    'amountOfCompletedLessons' => 0,
                ]);
                return view('courses.show', [
                    'course' => $course
                ]);
            } else {
                return view('courses.notEnoughCredits');
            }
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\courses $course
     * @return \Illuminate\Http\Response
     */
    public function edit(courses $course)
    {
        $languages = programmingLanguages::all();

        return view('courses.edit', [
            'course' => $course,
            'languages' => $languages
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\courses $courses
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, courses $course)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['required', 'string', 'max:300'],
            'duration' => ['required', 'string', 'max:11'],
            'difficulty' => ['required', 'numeric', 'between:0,3'],
            'price' => ['required', 'numeric', 'between:0,1000'],
            'programming_language_id' => ['required', 'exists:programming_languages,id'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048']
        ]);

        if ($request->hasFile('image')) {
            Storage::disk('public')->delete($course->image);
            $validated['image'] = $request->file('image')->store('courseImages', 'public');
        } else {
            $validated['image'] = $course->image;
        }

        $course->update($validated);

        return redirect(route('courses.show', [$course->id]));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\courses $courses
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(courses $course)
    {
        $course->delete();
        return redirect(route('courses.index'));
    }

    public function generateCertificate(courses $course)
    {
        PDF::setOptions(['dpi' => 5, 'defaultFont' => 'sans-serif']);
        $pdf = PDF::loadView('pdf.certificate', [
            'course' => $course,
        ]);
        return $pdf->stream();
    }

    public function completed(courses $course)
    {
        if (Auth::check() && $course->Completed()) {
            return view('courses.completed', [
                'course' => $course
            ]);
        } else {
            return redirect(route('courses.show', [$course->id]));
        }
    }

    public function feedback(Request $request, courses $course)
    {
        $validated = $request->validate([
            'comment' => ['required', 'string', 'max:2048'],
        ]);

        $validated['course_id'] = $course->id;
        courseFeedback::create($validated);

        return redirect(route('courses.index'));
    }
}
