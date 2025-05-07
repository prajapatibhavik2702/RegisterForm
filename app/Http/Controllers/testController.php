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


