.PHONY=deploy

project_root=../../www/

deploy:
	php console.php mep --project_root=$(dir) --branch --interactive