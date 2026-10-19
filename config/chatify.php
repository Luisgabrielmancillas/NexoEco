<?php

return [
    'web' => ['enabled' => false],
    // NexoEco exposes only product conversations through its own authenticated routes.
    'api' => ['middleware' => ['web', 'auth', \App\Http\Middleware\DisableChatifyApi::class]],
    'gravatar' => ['enabled' => false],
];
