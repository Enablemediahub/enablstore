import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';

import EnBadge from '@/Components/EnBadge.vue';

describe('EnBadge', () => {
    it('applies semantic tone styling', () => {
        const wrapper = mount(EnBadge, {
            props: {
                tone: 'warning',
            },
            slots: {
                default: 'Pending',
            },
        });

        expect(wrapper.classes()).toContain('bg-accent-100');
        expect(wrapper.text()).toBe('Pending');
    });
});
