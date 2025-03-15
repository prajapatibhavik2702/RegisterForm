<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <style>
        body {
            background-color: #f8f9fa;
        }

        .register-card {
            max-width: 500px;
            margin: auto;
            border-radius: 10px;
        }

        .form-label {
            font-weight: bold;
        }

        .form-control {
            border-radius: 8px;
        }

        .btn-primary {
            border-radius: 8px;
            padding: 10px;
        }
    </style>
</head>


<body class="bg-light">
    <div class="container mt-5">
        <div class="card shadow p-4 register-card">

            <div class="d-flex justify-content-between">
                <a href="/" class="btn btn-sm btn-link text-primary">User List</a>
            </div>

            <h2 class="mb-4 text-center text-primary">User Registration</h2>

            <form id="registerForm" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label" for="full_name">Full Name</label>
                    <input type="text" name="full_name" class="form-control" placeholder="Enter your full name">
                    <small class="text-danger error" id="full_name_error"></small>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="dob">Date of Birth</label>
                    <input type="date" name="dob" class="form-control">
                    <small class="text-danger error" id="dob_error"></small>
                </div>
                <div class="mb-3">
                    <label class="form-label">Gender</label><br>
                    <div class="form-check form-check-inline">
                        <input type="radio" name="gender" value="male" class="form-check-input"> Male
                    </div>
                    <div class="form-check form-check-inline">
                        <input type="radio" name="gender" value="female" class="form-check-input"> Female
                    </div>
                    <small class="text-danger error" id="gender_error"></small>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" name="email" class="form-control" placeholder="Enter your email">
                    <small class="text-danger error" id="email_error"></small>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="mobile">Mobile</label>
                    <input type="text" id="mobile" name="mobile" class="form-control"
                        placeholder="Enter 10-digit mobile number" maxlength="10"
                        oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                    <small class="text-danger error" id="mobile_error"></small>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="password">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Enter password">
                    <small class="text-danger error" id="password_error"></small>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="password_confirmation">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="form-control"
                        placeholder="Confirm your password">
                    <small class="text-danger error" id="password_confirmation_error"></small>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="profile_image">Profile Image</label>
                    <input type="file" name="profile_image" class="form-control">
                    <small class="text-danger error" id="profile_image_error"></small>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="captchaInput">Enter Captcha</label>
                    <input type="text" id="captchaInput" class="form-control">
                    <small class="text-danger error" id="captcha_error"></small>
                </div>

                <div class="mb-3 text-center">
                    <img id="captchaImage" src="" alt="Captcha" class="border p-1 rounded">
                    <input type="hidden" id="captchaKey">
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <small class="text-muted">Can't read? <button type="button" id="refreshCaptcha"
                            class="btn btn-sm btn-link">Refresh Captcha</button></small>
                </div>

                <div class="d-flex justify-content-between">
                    <button type="submit" id="submitBtn" class="btn btn-primary w-50 me-2">Register</button>
                    <button type="button" id="clearForm" class="btn btn-outline-danger w-50">Clear</button>
                </div>

            </form>
        </div>
    </div>


    <script>
        function showToast(message) {
            Toastify({
                text: message,
                duration: 3000,
                close: true,
                gravity: "top",
                position: "center",
                backgroundColor: "linear-gradient(to right, #00b09b, #96c93d)"
            }).showToast();
        }

        $("#clearForm").click(function() {
            $("#registerForm")[0].reset();
            $(".error").text("");
        });


        // Function to Load CAPTCHA
        function loadCaptcha() {
            $.get("/generate-captcha", function(data) {
                let timestamp = new Date().getTime();
                $("#captchaImage").attr("src", data.image_url + '?t=' + timestamp);
                $("#captchaKey").val(data.captcha_key);
            }).fail(function() {
                showToast("Captcha load failed!");
            });
        }

        $("#refreshCaptcha").click(loadCaptcha);

        $("#registerForm").submit(function(e) {
            e.preventDefault();

            $(".error").text("");

            let name = $("input[name='full_name']").val();
            let nameRegex = /^[A-Za-z\s]+$/;
            if (!nameRegex.test(name)) {
                $("#full_name_error").text("Only letters and spaces are allowed.");
                return;
            }

            let phone = $("#mobile").val();
            let phoneRegex = /^[6-9]\d{9}$/;
            if (!phoneRegex.test(phone)) {
                $("#mobile_error").text("Phone number must start with 6-9 and be 10 digits.");
                return;
            }

            let email = $("input[name='email']").val();
            let emailRegex = /^[a-zA-Z0-9._%+-]+@gmail\.com$/;
            if (!emailRegex.test(email)) {
                $("#email_error").text("Email must be a valid @gmail.com address.");
                return;
            }

            let password = $("input[name='password']").val();
            let confirmPassword = $("input[name='password_confirmation']").val();
            if (password !== confirmPassword) {
                $("#password_confirmation_error").text("Passwords do not match.");
                return;
            }

            let dob = $("input[name='dob']").val();
            if (!isValidAge(dob)) {
                $("#dob_error").text("You must be at least 18 years old.");
                return;
            }

            $('input[name="profile_image"]').on('change', function() {
                let file = this.files[0];
                let errorElement = $('#profile_image_error');
                errorElement.text('');

                if (file) {
                    let allowedTypes = ["image/jpeg", "image/png"];
                    if (!allowedTypes.includes(file.type)) {
                        errorElement.text("Only JPG and PNG allowed.");
                        $(this).val('');
                    } else if (file.size > 2 * 1024 * 1024) {
                        errorElement.text("Max size 2MB.");
                        $(this).val('');
                    }
                }
            });

            let captcha = $("#captchaInput").val();
            let captchaKey = $("#captchaKey").val();

            $.post("/verify-captcha", {
                _token: "{{ csrf_token() }}",
                captcha: captcha,
                captcha_key: captchaKey
            }).done(function(response) {
                submitForm();
            }).fail(function(response) {
                $("#captcha_error").text("Invalid captcha!");
                loadCaptcha();
            });
        });

        function isValidAge(dob) {
            let birthDate = new Date(dob);
            let today = new Date();
            let age = today.getFullYear() - birthDate.getFullYear();
            let monthDiff = today.getMonth() - birthDate.getMonth();

            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }

            return age >= 18;
        }

        function submitForm() {
            let formData = new FormData($("#registerForm")[0]);
            formData.append("_token", "{{ csrf_token() }}");

            $(".error").text("");

            $.ajax({
                url: "/submitRegister",
                method: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    window.location.href = "/";
                    showToast("Registration successful!");
                    loadCaptcha();
                },
                error: function(response) {
                    if (response.status === 422) {
                        let errors = response.responseJSON.errors;
                        $(".error").text("");
                        Object.keys(errors).forEach(field => {
                            $("#" + field + "_error").text(errors[field][0]);
                        });
                    } else {
                        showToast("Something went wrong!");
                    }
                    loadCaptcha();
                }
            });
        }
        $(document).ready(loadCaptcha);
    </script>
</body>

</html>
