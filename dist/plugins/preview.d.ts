import { App } from 'vue';
type FabriqMessage = 'preview-ready' | 'locate-block';
export type FabriqEvent = `fabriq-${FabriqMessage}`;
export type PreviewMessageData = {
    type: FabriqEvent;
    url: string;
    [x: string]: unknown;
};
export interface FabriqPreviewPluginOptions {
    fabriq_url: string;
    scroll_padding?: string;
}
export declare const FabriqPreview: {
    install: (app: App, options: FabriqPreviewPluginOptions) => void;
};
export {};
