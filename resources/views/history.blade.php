<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Task Manager — History</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body class="bg-slate-50 text-slate-900">
    <main class="max-w-md mx-auto p-4 pb-28">
        <div class="flex items-center justify-between mb-3">
            <h1 class="text-xl font-bold">History</h1>
            <form method="POST" action="{{ route('tasks.history.clear') }}" onsubmit="return confirm('Hapus semua history?');">
                @csrf @method('DELETE')
                <button class="text-xs px-2 py-1 rounded bg-rose-600 text-white">Clear</button>
            </form>
        </div>

        <form class="flex gap-2 mb-3" method="GET">
            <input name="q" value="{{ request('q') }}" class="flex-1 rounded border p-2" placeholder="Cari di history...">
            <button class="px-3 py-2 rounded bg-slate-900 text-white">Cari</button>
        </form>

        <ul id="historyList" class="bg-white rounded-2xl shadow divide-y relative">
            @forelse($tasks as $task)
            <li id="task-{{ $task->id }}" class="flex items-center justify-between gap-3 p-3">
                <div class="min-w-0">
                    <div class="font-medium truncate">{{ $task->title }}</div>
                    <div class="text-xs text-slate-500">Selesai pada: {{ optional($task->completed_at)->format('Y-m-d H:i') }}</div>
                </div>
                <button class="p-2" aria-label="Menu"
                    onclick="openKebab(event, {{ $task->id }})">
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor">
                        <circle cx="12" cy="6" r="1.5" />
                        <circle cx="12" cy="12" r="1.5" />
                        <circle cx="12" cy="18" r="1.5" />
                    </svg>
                </button>
            </li>
            @empty
            <li class="p-4 text-center text-slate-500">Belum ada task selesai.</li>
            @endforelse
        </ul>

        <div class="mt-4">{{ $tasks->links() }}</div>
    </main>

    <nav class="fixed bottom-0 inset-x-0 bg-white border-t shadow z-40">
        <div class="max-w-md mx-auto grid grid-cols-2 text-xs">
            <a href="{{ route('tasks.index') }}" class="flex flex-col items-center py-2 text-slate-600">
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>Aktif</span>
            </a>
            <a href="{{ route('tasks.history') }}" class="flex flex-col items-center py-2 text-blue-600">
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>History</span>
            </a>
        </div>
    </nav>

    <div id="kebab" class="hidden fixed z-50 bg-white rounded-xl shadow p-1 w-40">
        <button id="kbRestore" class="w-full text-left px-3 py-2 hover:bg-slate-100">Pulihkan</button>
        <button id="kbDelete" class="w-full text-left px-3 py-2 hover:bg-slate-100 text-rose-600">Hapus</button>
    </div>

    <script>
        async function csrfFetch(url, options = {}) {
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const headers = Object.assign({
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            }, options.headers || {});
            return fetch(url, {
                ...options,
                headers
            });
        }

        async function toggleStatus(id) {
            const res = await csrfFetch(`/tasks/${id}/toggle`, {
                method: 'PATCH'
            });
            if (!res.ok) {
                alert('Gagal update');
                return;
            }
            const data = await res.json();
            if (!data.is_done) document.getElementById(`task-${id}`)?.remove();
            hideKebab();
        }

        async function deleteTask(id) {
            const res = await csrfFetch(`/tasks/${id}`, {
                method: 'DELETE'
            });
            if (!res.ok) {
                alert('Gagal hapus');
                return;
            }
            document.getElementById(`task-${id}`)?.remove();
            hideKebab();
        }

        let kebabId = null;

        function openKebab(ev, id) {
            kebabId = id;
            const kb = document.getElementById('kebab');
            const rect = ev.currentTarget.getBoundingClientRect();
            kb.style.top = `${rect.bottom + 6}px`;
            kb.style.left = `${Math.min(rect.left, window.innerWidth - 170)}px`;
            kb.classList.remove('hidden');
            document.getElementById('kbRestore').onclick = () => toggleStatus(kebabId);
            document.getElementById('kbDelete').onclick = () => deleteTask(kebabId);
            setTimeout(() => {
                window.addEventListener('click', outsideOnce, {
                    once: true
                });
            }, 0);
            ev.stopPropagation();
        }

        function outsideOnce() {
            hideKebab();
        }

        function hideKebab() {
            document.getElementById('kebab').classList.add('hidden');
            kebabId = null;
        }
    </script>
</body>

</html>
