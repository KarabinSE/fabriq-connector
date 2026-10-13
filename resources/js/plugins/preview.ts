import { router } from '@inertiajs/vue3'

type FabriqMessage = 'preview-ready' | 'locate-block'

export type FabriqEvent = `fabriq-${FabriqMessage}`
type HandlerMapping = Record<FabriqEvent, (event: PreviewMessageData) => any>

export type PreviewMessageData = {
    type: FabriqEvent
    url: string
    [x: string]: unknown
}

type ScrollToBlockEvent = PreviewMessageData & {
    type: 'fabriq-locate-block'
    url: string
    block: string
}

export interface FabriqPreviewPluginOptions {
    fabriq_url: string
    scroll_padding?: string
}

const reload = (args: PreviewMessageData) => {
    router.reload({
        only: ['page', 'blocks'],
        headers: { 'X-Skip-Shared': '1' },
        async: true,
    })
}


const scrollToBlock = (event: ScrollToBlockEvent) => {
    if (typeof window === 'undefined') {
        return
    }

    const el = document.getElementById(event.block)

    if(!el) {
        return
    }

    el.style.setProperty('scroll-margin-top', 'var(--nav-height)')
    el.scrollIntoView({behavior: 'smooth'})
}


function getEventHander(event: PreviewMessageData) {
    return ({
        'fabriq-preview-ready': () => reload(event),
        'fabriq-locate-block': () => scrollToBlock(event as ScrollToBlockEvent),
    } satisfies HandlerMapping)[event.type]
        ?? (() => console.warn('no event handler for ', event.type))
}


export const FabriqPreview = {
    install: <TApp>(app: TApp, options: FabriqPreviewPluginOptions) => {
        if(typeof window === 'undefined') {
            return
        }

        const handleMessage = (event: MessageEvent<PreviewMessageData>) => {
            if (event.origin !== options.fabriq_url) {
                console.warn('host not allowed', event.origin)

                return
            }

            if (event.type !== 'message') {
                console.warn('event type not allowed', event.type)

                return
            }

            const handleFabriqEvent = getEventHander(event.data)

            handleFabriqEvent()
        }

        window.addEventListener('message', handleMessage)
    },
}
