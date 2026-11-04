@if(session('success'))
<div class="container mx-auto px-4 mt-4">
    <div class="bg-green-50 dark:bg-green-900/30 border-l-4 border-green-500 text-green-800 dark:text-green-300 p-4 rounded shadow-sm alert-auto-dismiss flex items-start dark-mode-transition">
        <i class="fas fa-check-circle text-xl mr-3 mt-0.5"></i>
        <div class="flex-1">
            <p class="font-medium">{{ session('success') }}</p>
        </div>
        <button onclick="this.parentElement.remove()" class="text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-200 ml-4">
            <i class="fas fa-times"></i>
        </button>
    </div>
</div>
@endif

@if(session('error'))
<div class="container mx-auto px-4 mt-4">
    <div class="bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 text-red-800 dark:text-red-300 p-4 rounded shadow-sm alert-auto-dismiss flex items-start dark-mode-transition">
        <i class="fas fa-exclamation-circle text-xl mr-3 mt-0.5"></i>
        <div class="flex-1">
            <p class="font-medium">{{ session('error') }}</p>
        </div>
        <button onclick="this.parentElement.remove()" class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-200 ml-4">
            <i class="fas fa-times"></i>
        </button>
    </div>
</div>
@endif

@if(session('info'))
<div class="container mx-auto px-4 mt-4">
    <div class="bg-blue-50 dark:bg-blue-900/30 border-l-4 border-blue-500 text-blue-800 dark:text-blue-300 p-4 rounded shadow-sm alert-auto-dismiss flex items-start dark-mode-transition">
        <i class="fas fa-info-circle text-xl mr-3 mt-0.5"></i>
        <div class="flex-1">
            <p class="font-medium">{{ session('info') }}</p>
        </div>
        <button onclick="this.parentElement.remove()" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200 ml-4">
            <i class="fas fa-times"></i>
        </button>
    </div>
</div>
@endif

@if($errors->any())
<div class="container mx-auto px-4 mt-4">
    <div class="bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 text-red-800 dark:text-red-300 p-4 rounded shadow-sm alert-auto-dismiss dark-mode-transition">
        <div class="flex items-start">
            <i class="fas fa-exclamation-triangle text-xl mr-3 mt-0.5"></i>
            <div class="flex-1">
                <p class="font-medium mb-2">Please correct the following errors:</p>
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            <button onclick="this.parentElement.parentElement.remove()" class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-200 ml-4">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
</div>
@endif
