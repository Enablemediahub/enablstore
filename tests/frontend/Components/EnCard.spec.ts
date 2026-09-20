import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';

import EnCard from '@/Components/EnCard.vue';

describe('EnCard', () => {
    it('renders semantic content and named regions', () => {
        const wrapper = mount(EnCard, {
            props: {
                as: 'article',
            },
            slots: {
                header: '<h2>Sales</h2>',
                default: '<p>Today</p>',
                footer: '<button>View</button>',
            },
        });

        expect(wrapper.element.tagName).toBe('ARTICLE');
        expect(wrapper.text()).toContain('Sales');
        expect(wrapper.text()).toContain('Today');
        expect(wrapper.text()).toContain('View');
    });
});
