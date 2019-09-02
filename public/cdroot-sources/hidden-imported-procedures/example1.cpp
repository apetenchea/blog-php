/* Load-time dynamic linking. */
#include <Windows.h>

int main()
{
	MessageBox(NULL, "HIDDEN", NULL, MB_OK);
	ExitProcess(0);
	return 0;
}
