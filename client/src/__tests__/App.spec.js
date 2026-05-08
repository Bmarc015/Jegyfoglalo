import { describe, it, expect } from 'vitest'

import { shallowMount } from '@vue/test-utils'
import App from '../App.vue'

describe('App', () => {
  it('megjeleníti az alkalmazás fő szerkezeti elemeit', () => {
    const wrapper = shallowMount(App, {
      global: {
        stubs: {
          Menu: true,
          Footer: true,
          ToastContanier: true,
          RouterView: {
            template: '<section data-test="router-view"></section>',
          },
        },
      },
    })

    expect(wrapper.findComponent({ name: 'Menu' }).exists()).toBe(true)
    expect(wrapper.find('[data-test="router-view"]').exists()).toBe(true)
    expect(wrapper.findComponent({ name: 'Footer' }).exists()).toBe(true)
  })
})
