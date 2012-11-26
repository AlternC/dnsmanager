#!/bin/bash

usage() {
    cat <<EOF
Usage: `basename $0` name
EOF
}

name="$1"

if [ -z "$name" ]; then
    usage
    exit 1
fi

if [ -e modules/${name} ]; then
    echo "modules/${name} already exists."
    exit 2
fi

#
mkdir -p modules/${name}
mkdir -p modules/${name}/views
mkdir -p modules/${name}/db
mkdir -p modules/${name}/scripts
mkdir -p modules/${name}/libs

touch modules/${name}/install
chmod +x modules/${name}/install

#
cat >> modules/${name}/hooks.php <<EOF
<?php
class ${name^}Hooks extends AHooks {
  public function menu(&\$menu) {
    \$menu[] = array(
		    'url' => '/${name}',
		    'name' => _("${name}"),
		    );
  }


}
EOF

#
cat >> modules/${name}/controller.php <<EOF
<?php
class ${name^}Controller extends AController {
  public function indexAction() {
    // ...
  }


}
EOF
