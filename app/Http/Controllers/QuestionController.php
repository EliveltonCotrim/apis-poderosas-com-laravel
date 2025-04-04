<?php

namespace App\Http\Controllers;

use App\Http\Requests\{StoreQuestionRequest, UpdateQuestionRequest};
use App\Http\Resources\QuestionResource;
use App\Models\Question;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class QuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $questions = Question::query()->where('status', 'published')->paginate(10);

            return QuestionResource::collection($questions);

        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
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

    public function archive(Question $question)
    {
        try {
            Gate::authorize('archive', $question);

            $question->delete();

            return response()->json([
                'message' => 'Question archived successfully',
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

    public function restore(int $question_id)
    {
        try {
            $question = Question::onlyTrashed()->findOrFail($question_id);

            Gate::authorize('restore', $question);
            $question->restore();

            return response()->json([
                'message' => 'Question restored successfully',
            ], Response::HTTP_OK);

        } catch (AuthorizationException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], Response::HTTP_FORBIDDEN);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], Response::HTTP_NOT_FOUND);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Publish the specified resource from storage.
     */
    public function publish(Question $question)
    {
        try {
            Gate::authorize('publish', $question);

            throw_unless(
                $question->status === 'draft',
                new Exception('Question is not in draft status', Response::HTTP_NOT_FOUND)
            );

            $question->update([
                'status' => 'published',
            ]);

            return response()->json([
                'message' => 'Question published successfully',
            ], Response::HTTP_NO_CONTENT);

        } catch (AuthorizationException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], Response::HTTP_FORBIDDEN);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }
}
