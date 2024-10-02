<?php

namespace App\Http\Controllers\Student;

use Carbon\Carbon;
use App\Models\Grade;
use App\Models\Answer;
use App\Models\Question;
use App\Models\ExamGroup;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ExamController extends Controller
{
    public function confirmation($id)
    {
        //get exam group
        $exam_group = ExamGroup::with('exam.lesson', 'exam_session', 'student.classroom')
                    ->where('student_id', auth()->guard('student')->user()->id)
                    ->where('id', $id)
                    ->first();

        //get grade / nilai
        $grade = Grade::where('exam_id', $exam_group->exam->id)
                    ->where('exam_session_id', $exam_group->exam_session->id)
                    ->where('student_id', auth()->guard('student')->user()->id)
                    ->first();
        
        //return with inertia
        return inertia('Student/Exams/Confirmation', [
            'exam_group' => $exam_group,
            'grade' => $grade,
        ]);
    }

    // original function
    public function startExam($id)
    {
        //get exam group
        $exam_group = ExamGroup::with('exam.lesson', 'exam_session', 'student.classroom')
                    ->where('student_id', auth()->guard('student')->user()->id)
                    ->where('id', $id)
                    ->first();

        //get grade / nilai
        $grade = Grade::where('exam_id', $exam_group->exam->id)
                    ->where('exam_session_id', $exam_group->exam_session->id)
                    ->where('student_id', auth()->guard('student')->user()->id)
                    ->first();

        //update start time di table grades
        $grade->start_time = Carbon::now();
        $grade->update();

        //cek apakah questions / soal ujian di random
        if($exam_group->exam->random_question == 'Y') {

            //get questions / soal ujian
            $questions = Question::where('exam_id', $exam_group->exam->id)->inRandomOrder()->get();

        } else {

            //get questions / soal ujian
            $questions = Question::where('exam_id', $exam_group->exam->id)->get();

        }

        //define pilihan jawaban default
        $question_order = 1;

        foreach ($questions as $question) {

            //buat array jawaban / answer
            $options = [1,2];
            if(!empty($question->option_3)) $options[] = 3;
            if(!empty($question->option_4)) $options[] = 4;
            if(!empty($question->option_5)) $options[] = 5;

            //acak jawaban / answer
            if($exam_group->exam->random_answer == 'Y') {
                shuffle($options);
            }

            //cek apakah sudah ada data jawaban
            $answer = Answer::where('student_id', auth()->guard('student')->user()->id)
                    ->where('exam_id', $exam_group->exam->id)
                    ->where('exam_session_id', $exam_group->exam_session->id)
                    ->where('question_id', $question->id)
                    ->first();

            //jika sudah ada jawaban / answer
            if($answer) {

                //update urutan question / soal
                $answer->question_order = $question_order;
                $answer->update();

            } else {

                //buat jawaban default baru
                Answer::create([
                    'exam_id'           => $exam_group->exam->id,
                    'exam_session_id'   => $exam_group->exam_session->id,
                    'question_id'       => $question->id,
                    'student_id'        => auth()->guard('student')->user()->id,
                    'question_order'    => $question_order,
                    'answer_order'      => implode(",", $options),
                    'answer'            => 0,
                    'is_correct'        => 'N'
                ]);

            }
            $question_order++;

        }

        //redirect ke ujian halaman 1
        return redirect()->route('student.exams.show', [
            'id'    => $exam_group->id, 
            'page'  => 1
        ]);   
    }
    
    // modify function

    // public function startExam($id)
    // {
    //     // Get exam group
    //     $exam_group = ExamGroup::with('exam.lesson', 'exam_session', 'student.classroom')
    //                 ->where('student_id', auth()->guard('student')->user()->id)
    //                 ->where('id', $id)
    //                 ->first();
    
    //     // Get grade / nilai
    //     $grade = Grade::where('exam_id', $exam_group->exam->id)
    //                 ->where('exam_session_id', $exam_group->exam_session->id)
    //                 ->where('student_id', auth()->guard('student')->user()->id)
    //                 ->first();
    
    //     // Calculate remaining duration based on exam start time
    //     $currentTime = Carbon::now();
    //     $startTime = Carbon::parse($exam_group->exam_session->start_time);
    //     $endTime = Carbon::parse($exam_group->exam_session->end_time);
    
    //     // Calculate the remaining time in seconds
    //     $remainingDuration = $endTime->diffInSeconds($currentTime);
    
    //     // Prevent negative duration
    //     if ($remainingDuration <= 0) {
    //         return redirect()->route('student.dashboard')->with('error', 'Ujian telah berakhir');
    //     }
    
    //     // Update start time and remaining duration in the grades table
    //     $grade->start_time = $currentTime;
    //     $grade->duration = $remainingDuration;
    //     $grade->update();
    
    //     // Cek apakah questions / soal ujian di random
    //     if($exam_group->exam->random_question == 'Y') {
    //         $questions = Question::where('exam_id', $exam_group->exam->id)->inRandomOrder()->get();
    //     } else {
    //         $questions = Question::where('exam_id', $exam_group->exam->id)->get();
    //     }
    
    //     // Define pilihan jawaban default
    //     $question_order = 1;
    //     foreach ($questions as $question) {
    //         $options = [1,2];
    //         if(!empty($question->option_3)) $options[] = 3;
    //         if(!empty($question->option_4)) $options[] = 4;
    //         if(!empty($question->option_5)) $options[] = 5;
    
    //         // Acak jawaban / answer
    //         if($exam_group->exam->random_answer == 'Y') {
    //             shuffle($options);
    //         }
    
    //         // Cek apakah sudah ada data jawaban
    //         $answer = Answer::where('student_id', auth()->guard('student')->user()->id)
    //                 ->where('exam_id', $exam_group->exam->id)
    //                 ->where('exam_session_id', $exam_group->exam_session->id)
    //                 ->where('question_id', $question->id)
    //                 ->first();
    
    //         // Jika sudah ada jawaban / answer
    //         if($answer) {
    //             $answer->question_order = $question_order;
    //             $answer->update();
    //         } else {
    //             Answer::create([
    //                 'exam_id'           => $exam_group->exam->id,
    //                 'exam_session_id'   => $exam_group->exam_session->id,
    //                 'question_id'       => $question->id,
    //                 'student_id'        => auth()->guard('student')->user()->id,
    //                 'question_order'    => $question_order,
    //                 'answer_order'      => implode(",", $options),
    //                 'answer'            => 0,
    //                 'is_correct'        => 'N'
    //             ]);
    //         }
    //         $question_order++;
    //     }
    
    //     // Redirect ke ujian halaman 1
    //     return redirect()->route('student.exams.show', [
    //         'id'    => $exam_group->id, 
    //         'page'  => 1
    //     ]);   
    // }
    

    /**
     * show
     */
    // original function
    public function show($id, $page)
    {
        //get exam group
        $exam_group = ExamGroup::with('exam.lesson', 'exam_session', 'student.classroom')
                    ->where('student_id', auth()->guard('student')->user()->id)
                    ->where('id', $id)
                    ->first();

        if(!$exam_group) {
            return redirect()->route('student.dashboard');
        }

        //get all questions
        $all_questions = Answer::with('question')
                        ->where('student_id', auth()->guard('student')->user()->id)
                        ->where('exam_id', $exam_group->exam->id)
                        ->orderBy('question_order', 'ASC')
                        ->get();

        //count all question answered
        $question_answered = Answer::with('question')
                        ->where('student_id', auth()->guard('student')->user()->id)
                        ->where('exam_id', $exam_group->exam->id)
                        ->where('answer', '!=', 0)
                        ->count();


        //get question active
        $question_active = Answer::with('question.exam')
                        ->where('student_id', auth()->guard('student')->user()->id)
                        ->where('exam_id', $exam_group->exam->id)
                        ->where('question_order', $page)
                        ->first();
        
        //explode atau pecah jawaban
        if ($question_active) {
            $answer_order = explode(",", $question_active->answer_order);
        } else  {
            $answer_order = [];
        }

        //get duration
        $duration = Grade::where('exam_id', $exam_group->exam->id)
                    ->where('exam_session_id', $exam_group->exam_session->id)
                    ->where('student_id', auth()->guard('student')->user()->id)
                    ->first();

        //return with inertia
        return inertia('Student/Exams/Show', [
            'id'                => (int) $id,
            'page'              => (int) $page,
            'exam_group'        => $exam_group,
            'all_questions'     => $all_questions,
            'question_answered' => $question_answered,
            'question_active'   => $question_active,
            'answer_order'      => $answer_order,
            'duration'          => $duration,
        ]); 
    }

    // modify function
//     public function show($id, $page)
// {
//     // Get exam group
//     $exam_group = ExamGroup::with('exam.lesson', 'exam_session', 'student.classroom')
//                 ->where('student_id', auth()->guard('student')->user()->id)
//                 ->where('id', $id)
//                 ->first();

//     if(!$exam_group) {
//         return redirect()->route('student.dashboard');
//     }

//     // Get all questions
//     $all_questions = Answer::with('question')
//                     ->where('student_id', auth()->guard('student')->user()->id)
//                     ->where('exam_id', $exam_group->exam->id)
//                     ->orderBy('question_order', 'ASC')
//                     ->get();

//     // Count all question answered
//     $question_answered = Answer::with('question')
//                     ->where('student_id', auth()->guard('student')->user()->id)
//                     ->where('exam_id', $exam_group->exam->id)
//                     ->where('answer', '!=', 0)
//                     ->count();

//     // Get question active
//     $question_active = Answer::with('question.exam')
//                     ->where('student_id', auth()->guard('student')->user()->id)
//                     ->where('exam_id', $exam_group->exam->id)
//                     ->where('question_order', $page)
//                     ->first();

//     // Explode atau pecah jawaban
//     $answer_order = $question_active ? explode(",", $question_active->answer_order) : [];

//     // Get duration
//     $duration = Grade::where('exam_id', $exam_group->exam->id)
//                 ->where('exam_session_id', $exam_group->exam_session->id)
//                 ->where('student_id', auth()->guard('student')->user()->id)
//                 ->first();

//     // Return with inertia
//     return inertia('Student/Exams/Show', [
//         'id'                => (int) $id,
//         'page'              => (int) $page,
//         'exam_group'        => $exam_group,
//         'all_questions'     => $all_questions,
//         'question_answered' => $question_answered,
//         'question_active'   => $question_active,
//         'answer_order'      => $answer_order,
//         'duration'          => $duration->duration,
//     ]); 
// }


    public function updateDuration(Request $request, $grade_id)
    {
        $grade = Grade::find($grade_id);
        $grade->duration = $request->duration;
        $grade->update();

        return response()->json([
            'success'  => true,
            'message' => 'Duration updated successfully.'
        ]);
    }

    public function answerQuestion(Request $request)
    {
        //update duration
        $grade = Grade::where('exam_id', $request->exam_id)
                ->where('exam_session_id', $request->exam_session_id)
                ->where('student_id', auth()->guard('student')->user()->id)
                ->first();

        $grade->duration = $request->duration;
        $grade->update();

        //get question
        $question = Question::find($request->question_id);
        
        //cek apakah jawaban sudah benar
        if($question->answer == $request->answer) {

            //jawaban benar
            $result = 'Y';
        } else {

            //jawaban salah
            $result = 'N';
        }

        //get answer
        $answer   = Answer::where('exam_id', $request->exam_id)
                    ->where('exam_session_id', $request->exam_session_id)
                    ->where('student_id', auth()->guard('student')->user()->id)
                    ->where('question_id', $request->question_id)
                    ->first();

        //update jawaban
        if($answer) {
            $answer->answer     = $request->answer;
            $answer->is_correct = $result;
            $answer->update();
        }

        return redirect()->back();
    }

    public function endExam(Request $request)
    {
        //count jawaban benar
        $count_correct_answer = Answer::where('exam_id', $request->exam_id)
                            ->where('exam_session_id', $request->exam_session_id)
                            ->where('student_id', auth()->guard('student')->user()->id)
                            ->where('is_correct', 'Y')
                            ->count();

        //count jumlah soal
        $count_question = Question::where('exam_id', $request->exam_id)->count();

        //hitung nilai
        $grade_exam = round($count_correct_answer/$count_question*100, 2);

        //update nilai di table grades
        $grade = Grade::where('exam_id', $request->exam_id)
                ->where('exam_session_id', $request->exam_session_id)
                ->where('student_id', auth()->guard('student')->user()->id)
                ->first();
        
        $grade->end_time        = Carbon::now();
        $grade->total_correct   = $count_correct_answer;
        $grade->grade           = $grade_exam;
        $grade->update();

        //redirect hasil
        return redirect()->route('student.exams.resultExam', $request->exam_group_id);
    }

    public function resultExam($exam_group_id)
    {
        //get exam group
        $exam_group = ExamGroup::with('exam.lesson', 'exam_session', 'student.classroom')
                ->where('student_id', auth()->guard('student')->user()->id)
                ->where('id', $exam_group_id)
                ->first();

        //get grade / nilai
        $grade = Grade::where('exam_id', $exam_group->exam->id)
                ->where('exam_session_id', $exam_group->exam_session->id)
                ->where('student_id', auth()->guard('student')->user()->id)
                ->first();

        //return with inertia
        return inertia('Student/Exams/Result', [
            'exam_group' => $exam_group,
            'grade'      => $grade,
        ]);
    }
}
