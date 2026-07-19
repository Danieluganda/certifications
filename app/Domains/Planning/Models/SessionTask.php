<?php

namespace App\Domains\Planning\Models;

use App\Domains\Curriculum\Models\Lesson;
use App\Domains\Curriculum\Models\Topic;
use App\Domains\Practice\Models\QuizAttempt;
use App\Domains\Projects\Models\ProjectMilestone;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SessionTask extends Model
{
    protected $fillable = [
        'study_session_id',
        'task_type',
        'lesson_id',
        'topic_id',
        'quiz_attempt_id',
        'project_milestone_id',
        'title',
        'target_value',
        'actual_value',
        'position',
        'status',
    ];

    public function studySession(): BelongsTo
    {
        return $this->belongsTo(StudySession::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    public function quizAttempt(): BelongsTo
    {
        return $this->belongsTo(QuizAttempt::class);
    }

    public function projectMilestone(): BelongsTo
    {
        return $this->belongsTo(ProjectMilestone::class);
    }
}
