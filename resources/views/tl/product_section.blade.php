@if ($project_Section['projects']->isEmpty())
    <div class="no-records">No records found</div>
@else
    @foreach ($project_Section['projects'] as $project)
        <div class="project_section">
            <p>Product name:{{ $project['product_name'] }}</p>
            <p>Peoples:{{ $project['number_of_people'] }}</p>
            <p>Project Completion
                Rate:{{ $project['completion_rate'] }}%
            </p>
            <a href="{{ route('tl.product', ['id' => $project['product_id']]) }}" class="btn btn-primary">View Details</a>
        </div>
    @endforeach
@endif
