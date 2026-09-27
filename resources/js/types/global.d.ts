import { PageProps as InertiaPageProps } from '@inertiajs/core';
import { AxiosInstance } from 'axios';
import { route as ziggyRoute } from 'ziggy-js';
import { PageProps as AppPageProps } from './';

declare global {
    interface Window {
        axios: AxiosInstance;
        PaystackPop?: {
            setup(options: {
                key: string;
                email: string;
                amount: number;
                currency: 'GHS';
                ref: string;
                callback: (response: { reference: string }) => void;
                onClose: () => void;
            }): { openIframe: () => void };
        };
    }

    var route: typeof ziggyRoute;
}

declare module 'vue' {
    interface ComponentCustomProperties {
        route: typeof ziggyRoute;
    }
}

declare module '@inertiajs/core' {
    interface PageProps extends InertiaPageProps, AppPageProps {}
}
