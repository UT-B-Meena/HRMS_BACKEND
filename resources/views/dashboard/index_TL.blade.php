@extends('layouts.app')

@section('content')
<style>
    .circle-initial {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: #007bff;
        color: white;
        display: flex;
        justify-content: center;
        align-items: center;
        font-weight: bold;
        margin-right: 10px;
    }
</style>

<div class="container">
    <h2>Dashboard</h2>

    <div class="row">
        <!-- First Card (Placeholder) -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    First Card
                </div>
                <div class="card-body">
                    <p>This is a placeholder for the first card content.</p>
                </div>
            </div>
        </div>

        <!-- Second Card (Placeholder) -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    Second Card
                </div>
                <div class="card-body">
                    <p>This is a placeholder for the second card content.</p>
                </div>
            </div>
        </div>

        <!-- Third Card: Team Rating -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    Team Rating
                </div>
                {{-- <div class="card-body">
                    @if($employees && count($employees) > 0)
                        <ul class="list-group">
                            @foreach($employees as $employee)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>{{ strtoupper(substr($employee->name, 0, 1)) }}. {{ $employee->name }}</span>
                                    <div>
                                        <span class="stars">
                                            @for($i = 1; $i <= 10; $i++)
                                                <i class="fa{{ $i <= $employee->rating ? 's' : 'r' }} fa-star"></i>
                                            @endfor
                                        </span>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p>No employees available for rating.</p>
                    @endif
                </div> --}}

                <div class="card-body">
                    <ul class="list-group">


                        @foreach($monthlyRating as $rating)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div class="d-flex flex-column">
                                    <span class="circle-initial">
                                        @php
                                            $nameParts = explode(' ',$rating->user->name);
                                            $initials = '';
                                            foreach ($nameParts as $part) {
                                                if (!empty($part)) {
                                                    $initials .= strtoupper($part[0]);
                                                }
                                            }
                                        @endphp
                                        {{ $initials }} <!-- Display the initials -->
                                    </span>
                                    <span><strong>{{ $rating->user->name }}</strong></span>
                                    <span class="stars">
                                        @php
                                            $roundedRating = round($rating->rating);
                                            $fullStars = floor($roundedRating );
                                            $halfStar = ($roundedRating  == 1) ? 1 : 0;
                                        @endphp

                                        @foreach(range(1, 10) as $i) <!-- Loop through 10 stars -->
                                            @if($i <= $fullStars)
                                                <i class="fa fa-star" style="color: gold;"></i> <!-- Full star -->
                                            @elseif($i == $fullStars + 1 && $halfStar)
                                                <i class="fa fa-star-half-alt" style="color: gold;"></i> <!-- Half star -->
                                            @else
                                                <i class="fa fa-star" style="color: lightgray;"></i> <!-- Empty star -->
                                            @endif
                                        @endforeach
                                    </span>
                                </div>
                                <span class="ml-auto">{{ $rating->rating }} / 10</span>
                            </li>
                        @endforeach
                    </ul>
                </div>





            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
@endsection
