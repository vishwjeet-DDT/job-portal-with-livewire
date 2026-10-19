@extends('layouts.admin')

@section('page_heading', 'Condidate Edit')
@section('content')

<div id="editModal">

    <livewire:user.edit  usertype="Candidates" :user="$user" />
</div>

    <livewire:user.resume-upload :user="$user" />
    <livewire:user.skill :user="$user"/>
    <livewire:user.education  :user="$user"/>
    <livewire:user.employment  :user="$user"/>
    <livewire:user.profile-summary :user="$user"/>
    @livewire('user.details',['user'=>$user])
@endsection
