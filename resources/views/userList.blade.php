<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User List</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
        }

        .card {
            border-radius: 10px;
            border: none;
        }

        thead {
            background-color: #343a40;
            color: #ffffff;
        }

        .pagination .page-item.active .page-link {
            background-color: #007bff;
            border-color: #007bff;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <div class="card shadow p-4">
            <h2 class="mb-4 text-center text-dark">User List</h2>

            <div class="d-flex justify-content-between mb-3">
                <input type="text" id="searchInput" class="form-control w-50" placeholder="🔍 Search users...">
                <a href="{{ route('register.form') }}" class="btn btn-primary">New Register</a>
            </div>

            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>BirthDate</th>
                        <th>Gender</th>
                        <th>Email</th>
                        <th>Mobile</th>

                    </tr>
                </thead>
                <tbody id="userTableBody">
                    <tr>
                        <td colspan="5" class="text-center text-secondary">Loading...</td>
                    </tr>
                </tbody>
            </table>

            <nav>
                <ul class="pagination justify-content-center" id="pagination"></ul>
            </nav>
        </div>
    </div>

    <script>
        function loadUsers(page = 1, search = "") {
            $.ajax({
                url: `/get-users?page=${page}&search=${search}`,
                method: "GET",
                success: function(response) {
                    let userTableBody = $("#userTableBody");
                    let pagination = $("#pagination");
                    userTableBody.empty();
                    pagination.empty();

                    if (response.data.length === 0) {
                        userTableBody.append(
                            '<tr><td colspan="5" class="text-center text-danger">No Users Found</td></tr>'
                        );
                        return;
                    }

                    let count = (response.current_page - 1) * response.per_page + 1;

                    response.data.forEach(user => {
                            let gender = user.gender.charAt(0).toUpperCase() + user.gender.slice(1);

                        userTableBody.append(`
                            <tr>
                                <td>${count}</td>
                                <td>${user.full_name}</td>
                                <td>${user.dob}
                                <td>${gender}</td>
                                <td>${user.email}</td>
                                <td>${user.mobile}</td>

                            </tr>
                        `);
                        count++;

                    });

                    if (response.last_page > 1) {
                        if (response.current_page > 1) {
                            pagination.append(`
                                <li class="page-item">
                                    <a class="page-link" href="#" onclick="loadUsers(${response.current_page - 1}, '${search}')">Prev</a>
                                </li>
                            `);
                        }

                        for (let i = 1; i <= response.last_page; i++) {
                            pagination.append(`
                                <li class="page-item ${i === response.current_page ? 'active' : ''}">
                                    <a class="page-link" href="#" onclick="loadUsers(${i}, '${search}')">${i}</a>
                                </li>
                            `);
                        }

                        if (response.current_page < response.last_page) {
                            pagination.append(`
                                <li class="page-item">
                                    <a class="page-link" href="#" onclick="loadUsers(${response.current_page + 1}, '${search}')">Next</a>
                                </li>
                            `);
                        }
                    }
                },
                error: function() {
                    $("#userTableBody").html(
                        '<tr><td colspan="5" class="text-center text-danger">Error Loading Data</td></tr>'
                    );
                }
            });
        }

        $(document).ready(function() {
            loadUsers();

            $("#searchInput").on("input", function() {
                let search = $(this).val();
                loadUsers(1, search);
            });
        });
    </script>
</body>

</html>
