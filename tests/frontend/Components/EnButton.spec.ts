import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';

import EnButton from '@/Components/EnButton.vue';

describe('EnButton', () => {
    it('emits clicks when enabled', async () => {
        const wrapper = mount(EnButton, {
            slots: {
                default: 'Save',
            },
        });

        await wrapper.trigger('click');

        expect(wrapper.emitted('click')).toHaveLength(1);
    });

    it('prevents clicks and announces loading state while loading', async () => {
        const wrapper = mount(EnButton, {
            props: {
                loading: true,
            },
        });

        await wrapper.trigger('click');

        expect(wrapper.attributes('disabled')).toBeDefined();
        expect(wrapper.attributes('aria-busy')).toBe('true');
        expect(wrapper.emitted('click')).toBeUndefined();
    });
});
