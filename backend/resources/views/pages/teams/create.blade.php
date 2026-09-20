@extends('layouts.app')

@section('title', 'Create Team — Super Score')

@section('content')
    <x-app-nav active="teams" />

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <h1 class="h4 mb-4">Create Team</h1>

                <div class="card">
                    <div class="card-body p-4">
                        <x-team-form :action="route('teams.store')" submit-label="Create Team" />
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
