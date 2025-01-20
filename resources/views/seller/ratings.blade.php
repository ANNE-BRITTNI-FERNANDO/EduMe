@extends('layouts.seller')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">My Ratings</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Order ID</th>
                                    <th>Rating</th>
                                    <th>Comment</th>
                                    <th>Buyer</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($ratings as $rating)
                                <tr>
                                    <td>{{ $rating->created_at->format('Y-m-d') }}</td>
                                    <td>#{{ $rating->order_id }}</td>
                                    <td>
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= $rating->rating)
                                                <i class="fas fa-star text-warning"></i>
                                            @else
                                                <i class="far fa-star"></i>
                                            @endif
                                        @endfor
                                    </td>
                                    <td>{{ $rating->comment }}</td>
                                    <td>
                                        @if($rating->is_anonymous)
                                            Anonymous
                                        @else
                                            {{ $rating->buyer->name }}
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{ $ratings->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
