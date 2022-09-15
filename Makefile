clean:
	rm -rf target

build:
	mkdir -p target
	docker build -t easyappointments-app docker
