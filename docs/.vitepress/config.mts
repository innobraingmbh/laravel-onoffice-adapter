import { defineConfig } from 'vitepress'

// https://vitepress.dev/reference/site-config
export default defineConfig({
  title: "Laravel onOffice Adapter",
  description: "Query onOffice like it was your ORM",
  themeConfig: {
    // https://vitepress.dev/reference/default-theme-config
    nav: [
      { text: 'Home', link: '/' },
      { text: 'Getting Started', link: '/getting-started' },
      { text: 'Repositories', link: '/repositories' },
      { text: 'Imprint', link: '/imprint' },
    ],

    sidebar: [
      {
        text: 'Introduction',
        items: [
          { text: 'Getting Started', link: '/getting-started' }
        ]
      },
      {
        text: 'Repositories',
        items: [
          { text: 'Activity', link: '/repositories/activity-repository' },
          { text: 'Address', link: '/repositories/address-repository' },
          { text: 'Base', link: '/repositories/base-repository' },
          { text: 'Estate', link: '/repositories/estate-repository' },
          { text: 'Field', link: '/repositories/field-repository' },
          { text: 'File', link: '/repositories/file-repository' },
          { text: 'Filter', link: '/repositories/filter-repository' },
          { text: 'Marketplace', link: '/repositories/marketplace-repository' },
          { text: 'Relation', link: '/repositories/relation-repository' },
          { text: 'SearchCriteria', link: '/repositories/search-criteria-repository' },
          { text: 'Setting', link: '/repositories/setting-repository' }
        ]
      }
    ],

    socialLinks: [
      { icon: 'github', link: 'https://github.com/innobraingmbh/laravel-onoffice-adapter' },
    ]
  }
})
