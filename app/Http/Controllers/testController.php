//One-to-One

Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->timestamps();
});

Schema::create('profiles', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('user_id');
    $table->string('phone')->nullable();
    $table->string('address')->nullable();
    $table->timestamps();

    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
});



class User extends Model
{
    protected $fillable = ['name', 'email'];

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }
}


class Profile extends Model
{
    protected $fillable = ['user_id', 'phone', 'address'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}


public function store(Request $request)
{
    // 1. Create User
    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
    ]);

    // 2. Create Profile Linked to User
    $user->profile()->create([
        'phone' => $request->phone,
        'address' => $request->address,
    ]);

    return response()->json(['message' => 'User and Profile created successfully', 'user' => $user->load('profile')]);
}


public function show($id)
{
    $user = User::with('profile')->findOrFail($id);
    return response()->json($user);
}

public function showProfile($id)
{
    $profile = Profile::with('user')->findOrFail($id);
    return response()->json($profile);
}



//One-to-Many

Schema::create('posts', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->text('body');
    $table->timestamps();
});

Schema::create('comments', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('post_id');
    $table->text('comment');
    $table->string('commenter')->nullable();
    $table->timestamps();

    $table->foreign('post_id')->references('id')->on('posts')->onDelete('cascade');
});


class Post extends Model
{
    protected $fillable = ['title', 'body'];

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}

class Comment extends Model
{
    protected $fillable = ['post_id', 'comment', 'commenter'];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}

public function store(Request $request)
{
    $post = Post::create([
        'title' => $request->title,
        'body' => $request->body,
    ]);

    // Optionally, add comments
    if ($request->comments) {
        foreach ($request->comments as $commentData) {
            $post->comments()->create($commentData);
        }
    }

    return response()->json(['message' => 'Post created', 'post' => $post->load('comments')]);
}

public function show($id)
{
    $post = Post::with('comments')->findOrFail($id);
    return response()->json($post);
}

public function showComment($id)
{
    $comment = Comment::with('post')->findOrFail($id);
    return response()->json($comment);
}

//**Many-to-Many**

Schema::create('students', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->timestamps();
});

Schema::create('courses', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->timestamps();
});

Schema::create('course_student', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('student_id');
    $table->unsignedBigInteger('course_id');
    $table->timestamps();

    $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
    $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
});


class Student extends Model
{
    protected $fillable = ['name'];

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'course_student');
    }
}

class Course extends Model
{
    protected $fillable = ['title'];

    public function students()
    {
        return $this->belongsToMany(Student::class, 'course_student');
    }
}

public function store(Request $request)
{
    // 1. Create Student
    $student = Student::create([
        'name' => $request->name,
    ]);

    // 2. Attach Courses
    $student->courses()->attach($request->course_ids); // Array of course IDs

    return response()->json([
        'message' => 'Student created and courses assigned',
        'student' => $student->load('courses'),
    ]);
}

public function show($id)
{
    $student = Student::with('courses')->findOrFail($id);
    return response()->json($student);
}

public function showCourse($id)
{
    $course = Course::with('students')->findOrFail($id);
    return response()->json($course);
}

//$student->courses()->attach([1, 2]) – Add new
//$student->courses()->sync([2, 3]) – Replace old with new
//$student->courses()->detach([1]) – Remove specific




//Has One Through
Schema::create('countries', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->timestamps();
});

Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->unsignedBigInteger('country_id');
    $table->timestamps();

    $table->foreign('country_id')->references('id')->on('countries')->onDelete('cascade');
});

Schema::create('phones', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('user_id');
    $table->string('phone_number');
    $table->timestamps();

    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
});


class Country extends Model
{
    protected $fillable = ['name'];

    public function phone()
    {
        return $this->hasOneThrough(Phone::class, User::class);
    }
}

class User extends Model
{
    protected $fillable = ['name', 'country_id'];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function phone()
    {
        return $this->hasOne(Phone::class);
    }
}

class Phone extends Model
{
    protected $fillable = ['user_id', 'phone_number'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

$country = Country::with('phone')->find(1);
return $country->phone;

//Has Many Through

Schema::create('countries', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->timestamps();
});

Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->unsignedBigInteger('country_id');
    $table->timestamps();

    $table->foreign('country_id')->references('id')->on('countries')->onDelete('cascade');
});

Schema::create('posts', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('user_id');
    $table->string('title');
    $table->text('body');
    $table->timestamps();

    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
});

class Country extends Model
{
    protected $fillable = ['name'];

    // Has many Users directly
    public function users()
    {
        return $this->hasMany(User::class);
    }

    // Has many Posts through Users
    public function posts()
    {
        return $this->hasManyThrough(Post::class, User::class);
    }
}


class User extends Model
{
    protected $fillable = ['name', 'country_id'];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}

class Post extends Model
{
    protected $fillable = ['user_id', 'title', 'content'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

$country = Country::with('posts')->find(1);

foreach ($country->posts as $post) {
    echo $post->title . '<br>';
}

/

$countries = Country::with('posts')->get();

foreach ($countries as $country) {
    echo "<h4>" . $country->name . "</h4>";

    foreach ($country->posts as $post) {
        echo $post->title . "<br>";
    }
}



//Polymorphic Relationships

Schema::create('posts', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->timestamps();
});

Schema::create('videos', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->timestamps();
});

Schema::create('comments', function (Blueprint $table) {
    $table->id();
    $table->text('body');

    // Polymorphic fields
    $table->unsignedBigInteger('commentable_id');
    $table->string('commentable_type');

    $table->timestamps();
});

class Post extends Model
{
    protected $fillable = ['title'];

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}

class Video extends Model
{
    protected $fillable = ['name'];

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}

class Comment extends Model
{
    protected $fillable = ['body'];

    public function commentable()
    {
        return $this->morphTo();
    }
}

$post = Post::find(1);

$post->comments()->create([
    'body' => 'Nice article!',
]);


$video = Video::find(1);

$video->comments()->create([
    'body' => 'Awesome video!',
]);

$post = Post::with('comments')->find(1);

foreach ($post->comments as $comment) {
    echo $comment->body . "<br>";
}

$comment = Comment::find(1);

$parent = $comment->commentable; // Will return Post or Video model


//Many-to-Many Polymorphic

Schema::create('posts', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->timestamps();
});

Schema::create('posts', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->timestamps();
});


Schema::create('tags', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->timestamps();
});


Schema::create('tags', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->timestamps();
});


Schema::create('taggables', function (Blueprint $table) {
    $table->unsignedBigInteger('tag_id');
    $table->unsignedBigInteger('taggable_id');
    $table->string('taggable_type');
});


class Tag extends Model
{
    protected $fillable = ['name'];

    public function posts()
    {
        return $this->morphedByMany(Post::class, 'taggable');
    }

    public function videos()
    {
        return $this->morphedByMany(Video::class, 'taggable');
    }
}


class Post extends Model
{
    protected $fillable = ['title'];

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }
}

class Video extends Model
{
    protected $fillable = ['name'];

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }
}

$post = Post::find(1);
$post->tags()->attach([1, 2]);

$video = Video::find(1);
$video->tags()->attach([2, 3]);

$post = Post::with('tags')->find(1);

foreach ($post->tags as $tag) {
    echo $tag->name;
}


$tag = Tag::with('posts')->find(1);

foreach ($tag->posts as $post) {
    echo $post->title;
}


$tag = Tag::with('videos')->find(1);

foreach ($tag->videos as $video) {
    echo $video->name;
}



