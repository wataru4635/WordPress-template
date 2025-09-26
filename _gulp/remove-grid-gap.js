/**
 * Custom PostCSS plugin to remove grid-gap properties
 */
module.exports = () => {
  return {
    postcssPlugin: 'remove-grid-gap',
    Declaration(decl) {
      // grid-gapプロパティを見つけて削除
      if (decl.prop === 'grid-gap') {
        decl.remove();
      }
    }
  };
};

module.exports.postcss = true;
