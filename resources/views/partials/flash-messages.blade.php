@if(session('success'))
    <div class="mx-auto w-full max-w-7xl px-4 pt-4 sm:px-6 lg:px-8">
        <div role="alert" class="alert alert-success alert-auto-dismiss border border-success/20 shadow-sm">
            <i class="fas fa-circle-check text-lg"></i>
            <span class="font-medium">{{ session('success') }}</span>
            <button type="button" onclick="this.parentElement.remove()" class="btn btn-ghost btn-xs btn-circle" aria-label="Dismiss">
                <i class="fas fa-xmark"></i>
            </button>
        </div>
    </div>
@endif

@if(session('error'))
    <div class="mx-auto w-full max-w-7xl px-4 pt-4 sm:px-6 lg:px-8">
        <div role="alert" class="alert alert-error alert-auto-dismiss border border-error/20 shadow-sm">
            <i class="fas fa-circle-exclamation text-lg"></i>
            <span class="font-medium">{{ session('error') }}</span>
            <button type="button" onclick="this.parentElement.remove()" class="btn btn-ghost btn-xs btn-circle" aria-label="Dismiss">
                <i class="fas fa-xmark"></i>
            </button>
        </div>
    </div>
@endif

@if(session('info'))
    <div class="mx-auto w-full max-w-7xl px-4 pt-4 sm:px-6 lg:px-8">
        <div role="alert" class="alert alert-info alert-auto-dismiss border border-info/20 shadow-sm">
            <i class="fas fa-circle-info text-lg"></i>
            <span class="font-medium">{{ session('info') }}</span>
            <button type="button" onclick="this.parentElement.remove()" class="btn btn-ghost btn-xs btn-circle" aria-label="Dismiss">
                <i class="fas fa-xmark"></i>
            </button>
        </div>
    </div>
@endif

@if($errors->any())
    <div class="mx-auto w-full max-w-7xl px-4 pt-4 sm:px-6 lg:px-8">
        <div role="alert" class="alert alert-error alert-auto-dismiss items-start border border-error/20 shadow-sm">
            <i class="fas fa-triangle-exclamation mt-1 text-lg"></i>
            <div class="flex-1">
                <p class="font-semibold">Please correct the following errors:</p>
                <ul class="mt-1 list-inside list-disc space-y-0.5 text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="btn btn-ghost btn-xs btn-circle" aria-label="Dismiss">
                <i class="fas fa-xmark"></i>
            </button>
        </div>
    </div>
@endif
