<x-app-guru-layout>
    <div class="px-3 py-5 mx-4 my-6 bg-white rounded-lg shadow xl:p-6">
        {{-- Breadcrumb --}}
        @php
            $breadcrumbs = [
                ['label' => 'Dashboard', 'route' => route('guru.dashboard')],
                ['label' => 'LMS', 'route' => route('guru.dashboard.lms')],
                ['label' => $mataPelajaran->nama_matpel . ' ' . $kelas->nama_kelas],
            ];
        @endphp

        <x-breadcrumb :breadcrumbs="$breadcrumbs" />



        <div class="px-3">
            {{-- Tabs --}}
            <div class="flex gap-2 mb-4 mt-6 overflow-x-auto whitespace-nowrap">
                <x-nav-button-lms route="guru.dashboard.lms.forum" :id="$id" label="Forum" />
                <x-nav-button-lms route="guru.dashboard.lms.forum.tugas" :id="$id" label="Tugas" />
                <x-nav-button-lms route="guru.dashboard.lms.forum.anggota" :id="$id" label="Anggota" />
            </div>

            {{-- Main --}}
            <div class="flex flex-col items-center">
                <div class="w-full max-w-2xl">
                    <div class="flex justify-between items-center my-4">
                        <h2 class="text-2xl font-bold">Siswa</h2>
                        <p class="text-gray-500 text-right">Total: {{ $jumlahAnggota }} Siswa</p>
                    </div>

                    <div class="grid grid-cols-1 gap-2">
                        @foreach ($anggotaKelas as $anggota)
                            <div class="flex items-center bg-white p-2 border-b border-slate-300">
                                <div class="flex-shrink-0">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M15.25 9C15.25 10.7949 13.7949 12.25 12 12.25V13.75C14.6234 13.75 16.75 11.6234 16.75 9H15.25ZM12 12.25C10.2051 12.25 8.75 10.7949 8.75 9H7.25C7.25 11.6234 9.37665 13.75 12 13.75V12.25ZM8.75 9C8.75 7.20507 10.2051 5.75 12 5.75V4.25C9.37665 4.25 7.25 6.37665 7.25 9H8.75ZM12 5.75C13.7949 5.75 15.25 7.20507 15.25 9H16.75C16.75 6.37665 14.6234 4.25 12 4.25V5.75ZM9 16.75H15V15.25H9V16.75ZM2.75 12C2.75 6.89137 6.89137 2.75 12 2.75V1.25C6.06294 1.25 1.25 6.06294 1.25 12H2.75ZM12 2.75C17.1086 2.75 21.25 6.89137 21.25 12H22.75C22.75 6.06294 17.9371 1.25 12 1.25V2.75ZM21.25 12C21.25 14.6233 20.159 16.9905 18.4039 18.6748L19.4425 19.7571C21.4801 17.8016 22.75 15.0485 22.75 12H21.25ZM18.4039 18.6748C16.7412 20.2705 14.4858 21.25 12 21.25V22.75C14.8882 22.75 17.5117 21.61 19.4425 19.7571L18.4039 18.6748ZM15 16.75C16.576 16.75 17.8915 17.8726 18.1876 19.3621L19.6588 19.0697C19.226 16.8918 17.3055 15.25 15 15.25V16.75ZM12 21.25C9.51425 21.25 7.25884 20.2705 5.59612 18.6748L4.55751 19.7571C6.48833 21.61 9.11182 22.75 12 22.75V21.25ZM5.59612 18.6748C3.84103 16.9905 2.75 14.6233 2.75 12H1.25C1.25 15.0485 2.51989 17.8016 4.55751 19.7571L5.59612 18.6748ZM9 15.25C6.69445 15.25 4.77403 16.8918 4.3412 19.0697L5.81243 19.3621C6.10846 17.8726 7.42396 16.75 9 16.75V15.25Z"
                                            fill="#2D264B" />
                                        <path
                                            d="M15.0001 16H9.00009C7.0593 16 5.44134 17.3822 5.0769 19.2159C6.87368 20.9403 9.31313 22 12.0001 22C14.6871 22 17.1265 20.9403 18.9233 19.2159C18.5588 17.3822 16.9409 16 15.0001 16Z"
                                            fill="#2D264B" />
                                        <path
                                            d="M18.9233 19.2159L19.4426 19.7571L19.739 19.4726L19.6589 19.0697L18.9233 19.2159ZM5.0769 19.2159L4.34129 19.0697L4.26122 19.4726L4.5576 19.7571L5.0769 19.2159ZM9.00009 16.75H15.0001V15.25H9.00009V16.75ZM18.404 18.6748C16.7413 20.2705 14.4858 21.25 12.0001 21.25V22.75C14.8883 22.75 17.5118 21.61 19.4426 19.7571L18.404 18.6748ZM15.0001 16.75C16.5761 16.75 17.8916 17.8726 18.1877 19.3621L19.6589 19.0697C19.2261 16.8918 17.3056 15.25 15.0001 15.25V16.75ZM12.0001 21.25C9.51434 21.25 7.25893 20.2705 5.59621 18.6748L4.5576 19.7571C6.48842 21.61 9.11191 22.75 12.0001 22.75V21.25ZM9.00009 15.25C6.69454 15.25 4.77412 16.8918 4.34129 19.0697L5.81252 19.3621C6.10855 17.8726 7.42405 16.75 9.00009 16.75V15.25Z"
                                            fill="#2D264B" />
                                        <path
                                            d="M16 9C16 11.2091 14.2091 13 12 13C9.79086 13 8 11.2091 8 9C8 6.79086 9.79086 5 12 5C14.2091 5 16 6.79086 16 9Z"
                                            fill="#2D264B" />
                                        <path
                                            d="M15.25 9C15.25 10.7949 13.7949 12.25 12 12.25V13.75C14.6234 13.75 16.75 11.6234 16.75 9H15.25ZM12 12.25C10.2051 12.25 8.75 10.7949 8.75 9H7.25C7.25 11.6234 9.37665 13.75 12 13.75V12.25ZM8.75 9C8.75 7.20507 10.2051 5.75 12 5.75V4.25C9.37665 4.25 7.25 6.37665 7.25 9H8.75ZM12 5.75C13.7949 5.75 15.25 7.20507 15.25 9H16.75C16.75 6.37665 14.6234 4.25 12 4.25V5.75Z"
                                            fill="#2D264B" />
                                    </svg>
                                </div>
                                <div class="ml-4 flex-1">
                                    <h3 class="text-lg font-semibold">{{ $anggota->nama_siswa }}</h3>
                                    <p class="text-gray-500">{{ $anggota->email }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>

                </div>
            </div>

        </div>
    </div>
</x-app-guru-layout>
