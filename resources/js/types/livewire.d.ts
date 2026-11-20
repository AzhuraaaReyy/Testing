/**
 * Tipe minimal API JavaScript Livewire v4 yang dipakai aplikasi (interceptor).
 * https://livewire.laravel.com/docs/4.x/javascript#interceptors
 */

export interface LivewireComponent {
    el: HTMLElement;
    $wire: { $refresh(): Promise<unknown> };
}

interface ErrorContext {
    response: Response;
    preventDefault(): void;
}

export interface MessageInterceptor {
    message: { component: LivewireComponent; actions: Set<{ name: string }> };
    onError(callback: (context: ErrorContext) => void): void;
    onFailure(callback: (context: { error: unknown }) => void): void;
}

export interface RequestInterceptor {
    onError(callback: (context: ErrorContext) => void): void;
}

export interface LivewireGlobal {
    interceptMessage(callback: (interceptor: MessageInterceptor) => void): () => void;
    interceptRequest(callback: (interceptor: RequestInterceptor) => void): () => void;
}

declare global {
    interface Window {
        Livewire?: LivewireGlobal;
    }
}
