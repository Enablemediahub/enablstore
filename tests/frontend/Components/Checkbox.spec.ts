import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';

import Checkbox from '@/Components/Checkbox.vue';

describe('Checkbox', () => {
    it('emits the updated checked state', async () => {
        const wrapper = mount(Checkbox, {
            props: {
                checked: false,
            },
        });

        await wrapper.find('input').setValue(true);

        expect(wrapper.emitted('update:checked')).toEqual([[true]]);
    });
});