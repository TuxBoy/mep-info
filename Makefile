.PHONY=deploy

project_root=../../www/

deploy:
	php console.php mep --project_root=$(project_root) --branch --interactive