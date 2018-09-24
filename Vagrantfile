# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure(2) do |config|
  config.vm.hostname = "xsolla-summer-school-2018"
  config.vm.box = "ubuntu/xenial64"
  config.vm.synced_folder ".", "/home/xsolla"
      config.vm.network "private_network", ip: "192.168.100.123"

  config.vm.provider "virtualbox" do |vb|
     vb.memory = "1024"
  end

  config.vm.provision "shell", inline: <<-SHELL
     sudo apt-get update && sudo apt-get install python-software-properties
     sudo add-apt-repository ppa:ondrej/php
     sudo apt-get update && sudo apt-get -y upgrade
     sudo apt-get install -y php7.2-cli php7.2-mbstring php7.2-curl php7.2-mysql php7.2-gd php7.2-intl php7.2-xml php7.2-zip
     echo "mysql-server-5.7 mysql-server/root_password password root" | sudo debconf-set-selections
     echo "mysql-server-5.7 mysql-server/root_password_again password root" | sudo debconf-set-selections
     sudo apt-get install -y mysql-server-5.7
     mysql -uroot -proot -e "GRANT ALL PRIVILEGES ON *.* TO 'root' IDENTIFIED BY 'root' WITH GRANT OPTION; flush privileges;"
     sudo sed -i 's/bind-address/# bind-address/g' /etc/mysql/mysql.conf.d/mysqld.cnf
     sudo service mysql restart
  SHELL
end
