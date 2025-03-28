<?php

namespace App\Http\Controllers;

use App\Http\Requests\{StoreQuestionRequest, UpdateQuestionRequest};
use App\Http\Resources\QuestionResource;
use App\Models\Question;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class QuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreQuestionRequest $request)
    {
        try {

            $question = user()->questions()->create([
                'question' => $request->question,
                'status' => $request->get('status', 'draft'),
            ]);

            // return new QuestionResource($question);
            return QuestionResource::make($question);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Question $question)
    {
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateQuestionRequest $request, Question $question)
    {
        try {
            $question->update([
                'question' => $request->question,
                'status' => $request->get('status', 'draft'),
            ]);

            return QuestionResource::make($question);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Question $question)
    {
        try {
            Gate::authorize('forceDelete', $question);

            $question->forceDelete();

            return response()->json([
                'message' => 'Question deleted successfully',
            ], Response::HTTP_NO_CONTENT);

        } catch (AuthorizationException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], Response::HTTP_FORBIDDEN);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
