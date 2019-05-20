<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\courseChapters
 *
 * @property int $id
 * @property int $course_id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\courseChapters newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\courseChapters newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\courseChapters query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\courseChapters whereCourseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\courseChapters whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\courseChapters whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\courseChapters whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\courseChapters whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class courseChapters extends Model
{
    public function Lessons() {
        return $this->hasMany('courseChapterLessons')->orderBy('id', 'ASC')->get();
    }
}
