// https://tailwindcss.com/docs/configuration
module.exports = {
  content: ['./index.php', './app/**/*.php', './acf-blocks/*.php', './views/**/*.php', './resources/**/*.{php,vue,js}'],
  safelist: [
    'grid-cols-1',
    'grid-cols-2',
    'grid-cols-3',
    'grid-cols-4',
    'grid-cols-5',
    'grid-cols-6',
    'md:grid-cols-3',
    'md:grid-cols-4',
    'md:grid-cols-5',
    'md:grid-cols-6',

  ],
  theme: {
    colors: {
      'white': '#fff',
      'neutral-grey': '#808080',
      'purple-light': '#EEE1FD',
      'black': '#000',
      'gray-light': '#D7D7D7',
      'main': '#152F61',
      'pfc-blue': '#1F4791',
      'dark-green-blue': '#19415A',
      'pfc-orange': '#DF8627',
    },
    extend: {
      colors: {}, // Extend Tailwind's default colors
      borderRadius: {
        'pfc': "3rem"
      }
    },
  },
  plugins: [],
};
