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
