xgettext -o locale/habari.pot --no-wrap --add-comments=@locale --language=PHP --from-code=utf-8 --keyword=_n:1,2 --keyword=_ne:1,2 --keyword=_t --keyword=_e $(find . -name "*.php")
xgettext -o locale/habari.pot --no-wrap --add-comments=@locale --language=Perl --join-existing --from-code=utf-8 --keyword=_n:1,2 --keyword=_ne:1,2 --keyword=_t --keyword=_e $(find . -name "*.js")

