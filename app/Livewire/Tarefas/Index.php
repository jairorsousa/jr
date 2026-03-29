<?php

namespace App\Livewire\Tarefas;

use App\Enums\Priority;
use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\TaskList;
use Livewire\Component;

class Index extends Component
{
    // List management
    public bool $showListModal = false;
    public bool $showDeleteListModal = false;
    public ?string $editingListId = null;
    public ?string $deletingListId = null;
    public string $listName = '';
    public string $listColor = '#ff6f00';

    // Task management
    public bool $showTaskModal = false;
    public bool $showDeleteTaskModal = false;
    public ?string $editingTaskId = null;
    public ?string $deletingTaskId = null;

    // Task form
    public string $taskTitle = '';
    public string $taskDescription = '';
    public string $taskPriority = 'medium';
    public string $taskDueDate = '';
    public string $taskListId = '';

    // Filters
    public ?string $selectedListId = null;
    public string $filterPriority = '';
    public string $filterStatus = '';

    protected function rules(): array
    {
        return match (true) {
            $this->showListModal => [
                'listName' => 'required|string|max:255',
                'listColor' => 'required|string|max:20',
            ],
            $this->showTaskModal => [
                'taskTitle' => 'required|string|max:255',
                'taskDescription' => 'nullable|string|max:2000',
                'taskPriority' => 'required|in:' . implode(',', array_column(Priority::cases(), 'value')),
                'taskDueDate' => 'nullable|date',
                'taskListId' => 'nullable|uuid|exists:task_lists,id',
            ],
            default => [],
        };
    }

    // ── List CRUD ──

    public function selectList(?string $id): void
    {
        $this->selectedListId = $this->selectedListId === $id ? null : $id;
    }

    public function openCreateList(): void
    {
        $this->resetListForm();
        $this->showListModal = true;
    }

    public function openEditList(string $id): void
    {
        $list = TaskList::findOrFail($id);
        $this->editingListId = $id;
        $this->listName = $list->name;
        $this->listColor = $list->color;
        $this->showListModal = true;
    }

    public function saveList(): void
    {
        $this->validate();

        $data = [
            'name' => $this->listName,
            'color' => $this->listColor,
        ];

        if ($this->editingListId) {
            TaskList::findOrFail($this->editingListId)->update($data);
            session()->flash('success', 'Lista atualizada com sucesso.');
        } else {
            $data['sort_order'] = TaskList::max('sort_order') + 1;
            TaskList::create($data);
            session()->flash('success', 'Lista criada com sucesso.');
        }

        $this->showListModal = false;
        $this->resetListForm();
    }

    public function confirmDeleteList(string $id): void
    {
        $this->deletingListId = $id;
        $this->showDeleteListModal = true;
    }

    public function deleteList(): void
    {
        $list = TaskList::findOrFail($this->deletingListId);
        // Tasks will have list_id set to null (nullOnDelete)
        $list->delete();

        if ($this->selectedListId === $this->deletingListId) {
            $this->selectedListId = null;
        }

        session()->flash('success', 'Lista excluida com sucesso.');
        $this->showDeleteListModal = false;
        $this->deletingListId = null;
    }

    private function resetListForm(): void
    {
        $this->reset(['editingListId', 'listName']);
        $this->listColor = '#ff6f00';
        $this->resetValidation();
    }

    // ── Task CRUD ──

    public function openCreateTask(): void
    {
        $this->resetTaskForm();
        $this->taskListId = $this->selectedListId ?? '';
        $this->showTaskModal = true;
    }

    public function openEditTask(string $id): void
    {
        $task = Task::findOrFail($id);
        $this->editingTaskId = $id;
        $this->taskTitle = $task->title;
        $this->taskDescription = $task->description ?? '';
        $this->taskPriority = $task->priority->value;
        $this->taskDueDate = $task->due_date?->format('Y-m-d') ?? '';
        $this->taskListId = $task->list_id ?? '';
        $this->showTaskModal = true;
    }

    public function saveTask(): void
    {
        $this->validate();

        $data = [
            'title' => $this->taskTitle,
            'description' => $this->taskDescription ?: null,
            'priority' => $this->taskPriority,
            'due_date' => $this->taskDueDate ?: null,
            'list_id' => $this->taskListId ?: null,
        ];

        if ($this->editingTaskId) {
            Task::findOrFail($this->editingTaskId)->update($data);
            session()->flash('success', 'Tarefa atualizada com sucesso.');
        } else {
            $data['status'] = 'pending';
            $data['sort_order'] = Task::where('list_id', $data['list_id'])->max('sort_order') + 1;
            Task::create($data);
            session()->flash('success', 'Tarefa criada com sucesso.');
        }

        $this->showTaskModal = false;
        $this->resetTaskForm();
    }

    public function toggleComplete(string $id): void
    {
        $task = Task::findOrFail($id);

        if ($task->status === TaskStatus::Done) {
            $task->update([
                'status' => TaskStatus::Pending,
                'completed_at' => null,
            ]);
        } else {
            $task->update([
                'status' => TaskStatus::Done,
                'completed_at' => now(),
            ]);
        }
    }

    public function setTaskStatus(string $id, string $status): void
    {
        $task = Task::findOrFail($id);
        $task->update([
            'status' => $status,
            'completed_at' => $status === 'done' ? now() : null,
        ]);
    }

    public function confirmDeleteTask(string $id): void
    {
        $this->deletingTaskId = $id;
        $this->showDeleteTaskModal = true;
    }

    public function deleteTask(): void
    {
        Task::findOrFail($this->deletingTaskId)->delete();
        session()->flash('success', 'Tarefa excluida com sucesso.');
        $this->showDeleteTaskModal = false;
        $this->deletingTaskId = null;
    }

    public function reorder(array $orderedIds): void
    {
        foreach ($orderedIds as $index => $id) {
            Task::where('id', $id)->update(['sort_order' => $index]);
        }
    }

    private function resetTaskForm(): void
    {
        $this->reset(['editingTaskId', 'taskTitle', 'taskDescription', 'taskDueDate', 'taskListId']);
        $this->taskPriority = 'medium';
        $this->resetValidation();
    }

    public function render()
    {
        $lists = TaskList::withCount(['tasks as pending_count' => function ($q) {
            $q->where('status', '!=', 'done');
        }])->orderBy('sort_order')->get();

        $tasksQuery = Task::with('taskList')
            ->when($this->selectedListId, fn ($q) => $q->where('list_id', $this->selectedListId))
            ->when($this->filterPriority, fn ($q) => $q->where('priority', $this->filterPriority))
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus));

        // Default: show incomplete first, then completed
        $tasks = $tasksQuery
            ->orderByRaw("CASE status WHEN 'done' THEN 1 ELSE 0 END")
            ->orderBy('sort_order')
            ->orderByRaw("CASE priority
                WHEN 'urgent' THEN 1
                WHEN 'high' THEN 2
                WHEN 'medium' THEN 3
                WHEN 'low' THEN 4
                ELSE 5 END")
            ->orderBy('due_date')
            ->get();

        $priorities = Priority::cases();
        $statuses = TaskStatus::cases();
        $allLists = TaskList::orderBy('name')->get();

        return view('livewire.tarefas.index', compact('lists', 'tasks', 'priorities', 'statuses', 'allLists'));
    }
}
