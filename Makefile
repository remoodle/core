fresh-db:
	./vendor/bin/phinx rollback -t 0
	./vendor/bin/phinx migrate