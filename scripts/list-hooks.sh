grep -rC 2 'Hooks::call' *
echo "================"
grep -r 'Hooks::call' * | sed 's/Hooks::call(\(.*\)).*/\1/' | cut -d ',' -f 1 | tr -d "'\""
