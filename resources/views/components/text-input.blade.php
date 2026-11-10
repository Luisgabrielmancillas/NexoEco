@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-control dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-primary-600 dark:focus:border-primary-600 focus:ring-primary-600 dark:focus:ring-primary-600 rounded-md shadow-sm']) }}>
