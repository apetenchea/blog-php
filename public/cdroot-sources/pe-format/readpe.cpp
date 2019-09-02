#include <windows.h>
#include <io.h>
#include <fcntl.h>
#include <sys/stat.h>
#include <share.h>
#include <stdio.h>
#include <stdlib.h>
#include <stdint.h>

/* Print an error message and exit. */
void exit_error() {
  printf("Error: %d\n", GetLastError());
  ExitProcess(GetLastError());
}

/*
 * Dump data to a file.
 *
 * @param dump_name Name of the file.
 * @param buf Pointer to data.
 * @param count Size of the data in bytes.
 */
void dump(char *dump_name, const void *buf, unsigned int count) {
  int file_handle = 0;
  if (_sopen_s(
        &file_handle,
        dump_name,
        _O_CREAT | _O_BINARY | _O_WRONLY,
        _SH_DENYWR,
        _S_IREAD | _S_IWRITE
        ) != 0) {
    printf("Unable to open %s\n", dump_name);
    return;
  }
  if (_write(file_handle, buf, count) == -1) {
    printf("Unable to write to %s\n", dump_name);
  }
  if (_close(file_handle) != 0) {
    printf("Unable to close %s\n", dump_name);
  }
}

int main(int argc, char *argv[])
{
  if (argc < 2) {
    printf("Usage: %s file.exe\n", argv[0]);
    return 1;
  }

  /* Open a handle to the PE file. */
  HANDLE file_handle = CreateFileA(
      (LPCSTR) argv[1],
      GENERIC_READ,
      FILE_SHARE_READ,
      NULL,
      OPEN_EXISTING,
      FILE_ATTRIBUTE_NORMAL,
      NULL
      );
  if (file_handle == INVALID_HANDLE_VALUE) {
    exit_error();
  }

  /* Create a memory mapping to the file. */
  HANDLE file_mapping = CreateFileMapping(
      file_handle,
      NULL,
      PAGE_READONLY,
      0,
      0,
      NULL
      );
  if (file_mapping == NULL) {
    exit_error();
  }

  /* Map file into the address space of the application. */
  HANDLE file_start = MapViewOfFile(
      file_mapping,
      FILE_MAP_READ,
      0,
      0,
      0
      );
  if (file_start == NULL) {
    exit_error();
  }

  /* Dump the DOS header. */
  PIMAGE_DOS_HEADER dos_header = (PIMAGE_DOS_HEADER)file_start;
  if (dos_header->e_magic != (WORD) 0x5A4D) {
    puts("Wrong DOS header magic number!");
    printf("%d\n", dos_header->e_magic);
    return 1;
  }
  dump("dos_header.txt", dos_header, sizeof(IMAGE_DOS_HEADER));

  /* Get the PE header. */
  PIMAGE_NT_HEADERS32 pe_header = (PIMAGE_NT_HEADERS32) ((uint8_t *) file_start + dos_header->e_lfanew);
  if (pe_header->Signature != 0x4550) {
    puts("Wrong PE header signature!");
    printf("%d\n", pe_header->Signature);
    return 1;
  }
  if (pe_header->FileHeader.Machine != IMAGE_FILE_MACHINE_I386) {
    puts("Input should be an i386 PE file!");
    printf("%d\n", pe_header->FileHeader.Machine);
    return 1;
  }

  /* Dump the file header. */
  dump("file_header.txt", &(pe_header->FileHeader), sizeof(pe_header->FileHeader));

  /* Dump the optional header. */
  dump("optional_header.txt", &(pe_header->OptionalHeader), sizeof(pe_header->OptionalHeader));

  /* Dump all sections. */
  PIMAGE_SECTION_HEADER section_header = (PIMAGE_SECTION_HEADER)((uint8_t *) pe_header + sizeof(IMAGE_NT_HEADERS32));
  for (int index = 0; index < pe_header->FileHeader.NumberOfSections; ++index) {
    char name[IMAGE_SIZEOF_SHORT_NAME + 1] = { 0 };
    strncpy(name, (char *)section_header[index].Name, IMAGE_SIZEOF_SHORT_NAME);
    dump(
        name,
        (uint8_t *)file_start + section_header[index].PointerToRawData,
        section_header[index].SizeOfRawData
        );
  }

  UnmapViewOfFile(file_start);
  if (CloseHandle(file_mapping) == 0) {
    exit_error();
  }
  if (CloseHandle(file_handle) == 0) {
    exit_error();
  }
  puts("PE dumped\n");
  return 0;
}
