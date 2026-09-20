@extends('layouts.app')

@section('title', 'Edit Team — Super Score')

@section('content')
    <x-app-nav active="teams" />

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h4 mb-0">Edit Team</h1>
                    <a href="{{ route('teams.show', $team) }}" class="btn btn-sm btn-outline-secondary">Back to Squad</a>
                </div>

                <div class="card">
                    <div class="card-body p-4">
                        <x-team-form :action="route('teams.update', $team)" method="PUT" :team="$team" submit-label="Save Changes" />
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
