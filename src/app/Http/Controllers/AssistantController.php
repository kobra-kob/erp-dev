<?php

namespace App\Http\Controllers;

use App\Services\AssistantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssistantController extends Controller
{
    public function __construct(private readonly AssistantService $assistant) {}

    public function index(): View
    {
        return view('assistant.index', [
            'aiEnabled'   => $this->assistant->isAiEnabled(),
            'suggestions' => [
                'Quels clients doivent payer ?',
                'Quels produits sont en stock faible ?',
                'Commande le stock faible',
                'Quel est mon chiffre d\'affaires cette année ?',
            ],
        ]);
    }

    public function message(Request $request): JsonResponse
    {
        $data = $request->validate([
            'message'           => ['required', 'string', 'max:1000'],
            'history'           => ['array'],
            'history.*.role'    => ['required_with:history', 'in:user,assistant'],
            'history.*.content' => ['required_with:history', 'string', 'max:4000'],
        ]);

        $result = $this->assistant->ask($data['message'], $data['history'] ?? []);

        return response()->json($result);
    }
}
