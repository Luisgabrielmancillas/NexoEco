<button {{ $attributes->merge(['type' => 'submit', 'class' => 'ds-primary-button inline-flex items-center px-4 py-2 border rounded-md font-semibold text-xs uppercase tracking-widest focus:ring-2 focus:ring-primary-600 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
