const path = require('path');

module.exports = {
  entry: {
    'register-panel': './includes/support-admin-ui/assets/js/register-panel.js',
  },
  output: {
    path: path.resolve(__dirname, 'includes/support-admin-ui/assets/js/dist'),
    filename: '[name].min.js',
    clean: true,
  },
  module: {
    rules: [
      {
        test: /\.js$/,
        exclude: /node_modules/,
        use: {
          loader: 'babel-loader',
          options: {
            presets: ['@babel/preset-env', '@babel/preset-react'],
          },
        },
      },
      {
        test: /\.css$/,
        use: ['style-loader', 'css-loader'],
      },
    ],
  },
  externals: {
    react: 'React',
    'react-dom': 'ReactDOM',
    '@wordpress/components': 'wp.components',
    '@wordpress/hooks': 'wp.hooks',
    '@wordpress/i18n': 'wp.i18n',
    '@woocommerce/components': 'wc.components',
  },
  resolve: {
    extensions: ['.js', '.jsx'],
  },
  optimization: {
    minimize: true,
  },
};
