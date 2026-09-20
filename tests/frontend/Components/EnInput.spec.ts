import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';

import EnInput from '@/Components/EnInput.vue';

describe('EnInput', () => {
    it('renders an accessible error message', () => {
        const wrapper = mount(EnInput, {
            props: {
                id: 'email',
                label: 'Email address',
                modelValue: '',
                error: 'Enter a valid email address.',
            },
        });

        expect(wrapper.find('input').attributes('aria-invalid')).toBe('true');
        expect(wrapper.find('[role="alert"]').text()).toContain(
            'Enter a valid email address.',
        );
    });

    it('emits model updates', async () => {
        const wrapper = mount(EnInput, {
            props: {
                id: 'name',
                label: 'Name',
                modelValue: '',
            },
        });

        await wrapper.find('input').setValue('Ama');

        expect(wrapper.emitted('update:modelValue')).toEqual([['Ama']]);
    });
});
