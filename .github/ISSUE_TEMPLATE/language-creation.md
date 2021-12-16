---
name: Language creation
about: This is the layout that you need to have when creating a new language for the
  plugin.
title: ''
labels: ''
assignees: ''

---

## Language Name

Code: `lang code (e.g. en)`

[**Your github**](https://github.com/example) (*Translation - Language*) [Discord#0000]

###
### Note:

**Markdown:** `[**Your github**](https://github.com/yougithubhere) (*Translation - Language*) [Discord#0000]`

All languages created must be properly translated into your language, you are NOT to use Google Translate in the final product. The files that need to be changed are:
- [https://github.com/WolfDen133/WFT#credit](https://github.com/WolfDen133/WFT#credit) to give yourself credit for the translation.
- [WFT/src/Lang/LanguageManager (Line12)](https://github.com/WolfDen133/WFT/blob/main/src/WolfDen133/WFT/Lang/LanguageManager.php#L12) so the plugin can recognize that it needs to save that language by default.
- [WFT/resources](https://github.com/WolfDen133/WFT/tree/main/resources) To add the actual language file

Once all three are completed, and have been checked over by me (WolfDen133), and I am happy with the result, then the pull will be merged.
