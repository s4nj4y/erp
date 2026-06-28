<x-public-layout title="Profil Akun">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Profil Akun</h1>

    <div class="space-y-6 max-w-3xl">
        <div class="card p-6 sm:p-8">
            <div class="max-w-xl">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <div class="card p-6 sm:p-8">
            <div class="max-w-xl">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <div class="card p-6 sm:p-8">
            <div class="max-w-xl">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</x-public-layout>
