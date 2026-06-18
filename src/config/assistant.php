<?php

/*
|--------------------------------------------------------------------------
| Assistant Artisan (IA)
|--------------------------------------------------------------------------
|
| L'assistant fonctionne SANS clé (réponses calculées depuis la base de
| données = gratuit). Si une clé d'API compatible OpenAI est fournie, il
| formule des réponses plus naturelles à partir des mêmes données.
|
| Fournisseurs gratuits compatibles (format /v1/chat/completions) :
|  - Groq       : base_url https://api.groq.com/openai/v1   modèle ex. llama-3.3-70b-versatile
|  - OpenRouter : base_url https://openrouter.ai/api/v1     modèle ex. meta-llama/llama-3.3-70b-instruct:free
|  - OpenAI     : base_url https://api.openai.com/v1        modèle ex. gpt-4o-mini  (payant)
|  - Ollama     : base_url http://host.docker.internal:11434/v1  (local, hors ligne)
|
*/

return [
    'api_key'  => env('ASSISTANT_API_KEY', ''),
    'base_url' => env('ASSISTANT_BASE_URL', 'https://api.groq.com/openai/v1'),
    'model'    => env('ASSISTANT_MODEL', 'llama-3.3-70b-versatile'),
    'timeout'  => (int) env('ASSISTANT_TIMEOUT', 30),
];
