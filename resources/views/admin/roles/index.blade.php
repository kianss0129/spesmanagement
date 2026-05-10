<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Role Management</title>
    <link href="/css/app.css" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans">

<div class="max-w-6xl mx-auto p-6">

    <h1 class="text-2xl font-bold mb-4">Role Management</h1>

    {{-- SUCCESS / ERROR MESSAGE --}}
    @if(session('success'))
        <div class="bg-green-100 text-green-800 p-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 text-red-800 p-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <!-- ===================== -->
    <!-- Assign Role Form -->
    <!-- ===================== -->
    <div class="mb-6 bg-white p-4 rounded shadow">
        <h2 class="font-semibold mb-3">Assign Role</h2>

        <form method="POST" action="{{ route('admin.roles.assign') }}">
            @csrf

            <select name="user_id" class="border p-2 rounded mr-2">
                <option value="">Select user</option>
                @foreach($users as $u)
                    <option value="{{ $u->id }}">
                        {{ $u->name }} ({{ $u->email }})
                    </option>
                @endforeach
            </select>

            <select name="role" class="border p-2 rounded mr-2">
                <option value="">Select role</option>
                @foreach($roles as $r)
                    <option value="{{ $r }}">{{ $r }}</option>
                @endforeach
            </select>

            <button
                type="submit"
                class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-1 rounded"
            >
                Assign Role
            </button>
        </form>
    </div>

    <!-- ===================== -->
    <!-- Users Table -->
    <!-- ===================== -->
    <div class="bg-white p-4 rounded shadow">
        <h2 class="font-semibold mb-3">Current Users & Roles</h2>

        <table class="min-w-full text-sm border">
            <thead class="bg-gray-100 text-left">
            <tr>
                <th class="p-2 border-b">Name</th>
                <th class="p-2 border-b">Email</th>
                <th class="p-2 border-b">Current Role</th>
                <th class="p-2 border-b">Actions</th>
            </tr>
            </thead>

            <tbody>
            @foreach($users as $user)
                <tr class="hover:bg-gray-50">
                    <td class="p-2 border-b">
                        {{ $user->name }}
                    </td>

                    <td class="p-2 border-b">
                        {{ $user->email }}
                    </td>

                    <td class="p-2 border-b text-indigo-700 font-medium">
                        {{ $user->role ?? '—' }}
                    </td>

                    <td class="p-2 border-b space-x-2">

                        {{-- DELETE ROLE --}}
                        @if($user->role)
                            <form method="POST"
                                  action="{{ route('admin.roles.remove', $user->id) }}"
                                  style="display:inline"
                                  onsubmit="return confirm('Remove this user\'s role only?')">
                                @csrf
                                @method('DELETE')
                                <button
                                    class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded">
                                    Delete Role
                                </button>
                            </form>
                        @endif


                        {{-- DELETE USER --}}
                        @if(!auth()->user()->is($user) && !$user->hasRole('Super Admin'))
                            <form method="POST"
                                  action="{{ route('admin.users.destroy', $user->id) }}"
                                  style="display:inline"
                                  onsubmit="return confirm('⚠️ This will permanently delete this user. Continue?')">
                                @csrf
                                @method('DELETE')
                                <button
                                    class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded">
                                    Delete User
                                </button>
                            </form>
                        @endif

                    </td>
                </tr>
            @endforeach

            @if(count($users) === 0)
                <tr>
                    <td colspan="4" class="p-4 text-center text-gray-500">
                        No users found.
                    </td>
                </tr>
            @endif

            </tbody>
        </table>
    </div>

</div>

</body>
</html>
