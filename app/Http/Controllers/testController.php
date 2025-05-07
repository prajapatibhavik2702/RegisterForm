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



//


