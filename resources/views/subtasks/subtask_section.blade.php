@foreach ($groupedSubtasks as $key => $subtasks)
                <div class="col-lg-3 ">
                    <h4 class="p-4">{{ $key }} <span
                            class="badge bg-primary">{{ count($subtasks) }}</span></h4>
                    <div class="pending_div ">
                        @foreach ($subtasks as $subtask)
                            <div class="p-1 card m-1 @if ($subtask->extended_status == 1 && $subtask->status == 1) extended_hours_div @endif">
                                {{ $subtask->product->name }}<br>
                                Project: {{ $subtask->project->name }}<br>
                                Assinee: {{ $subtask->user->name }}
                                @if ($subtask->extended_status == 1 && $subtask->status == 1)
                                    <span class="text-danger">Time Extended </span>
                                @endif
                                @if ($subtask->status == 0)
                                    <br>
                                    Task: <br>
                                    Sub Task: {{ $subtask->name }}<br>
                                    Est. Duration: {{ $subtask->estimated_hours }} Hrs<br>
                                    Team: {{ $subtask->team->name }}<br>
                                    Reporting Person: {{ $subtask->assigned_user->name }}<br>
                                    Priority:{{ $subtask->priority }}
                                @endif
                                <br>
                                @if (Auth::user()->role_id == 2 && $subtask->reopen_status == 0)
                                    @if ($subtask->status == 0 || $subtask->status == 1)
                                        <button onclick="showSubTask({{ $subtask->id }}, {{ $subtask->status }})"
                                            class="btn editSubTask"><i class="fa fa-edit"></i></button>
                                        @endif @if ($subtask->status == 0)
                                            <button onclick="deleteSubTask({{ $subtask->id }})" class="btn"> <i
                                                    class="fa fa-trash"></i> </button>
                                        @endif
                                    @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
