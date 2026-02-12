<!-- meta tags and other links -->
<!DOCTYPE html>
<html lang="en" data-theme="light">

<x-head />

<body>

    <div class="custom-bg">
        <div class="container container--xl">
            <div class="d-flex align-items-center justify-content-between py-24">
                <a href="" class="" style="width: 20%;">
                    <img src="{{ asset('assets/images/logo.png') }}" alt="">
                </a>
                <a href="{{ route('login') }}" class="btn btn-outline-primary-600 text-sm"> LogIn </a>
            </div>

            <div class="py-res-120" style="padding-top:0;">
                <div class="row align-items-center">
                    <div class="col-lg-6">
                        <h3 class="mb-32 max-w-1000-px">We Make Every Connection into Lasting Relationships!</h3>
                        <p class="text-neutral-500 max-w-700-px text-lg">At Norlox Solutions, we believe business is not only built on data alone, but it calls for TRUST. We transform every interaction into insights; every lead into loyalty, and every customer into a lifelong partner. Because meaningful growth starts with meaningful connections. </p>

                        <div class="countdown my-56 d-flex align-items-center flex-wrap gap-md-4 gap-3" id="coming-soon">
                            <div class="d-flex flex-column align-items-center">
                                <h4 class="day-name mb-0 w-110-px fw-medium h-110-px bg-white text-black rounded-circle border border-dark d-flex justify-content-center align-items-center">Mon</h4>
                                <span class="text-neutral-500 text-md text-uppercase fw-medium mt-8">Day</span>
                            </div>
                            <div class="d-flex flex-column align-items-center">
                                <h4 class="month-name mb-0 w-110-px fw-medium h-110-px bg-white text-black rounded-circle border border-dark d-flex justify-content-center align-items-center">Oct</h4>
                                <span class="text-neutral-500 text-md text-uppercase fw-medium mt-8">Month</span>
                            </div>
                            <div class="d-flex flex-column align-items-center">
                                <h4 class="date-number mb-0 w-110-px fw-medium h-110-px bg-white text-black rounded-circle border border-dark d-flex justify-content-center align-items-center">06</h4>
                                <span class="text-neutral-500 text-md text-uppercase fw-medium mt-8">Date</span>
                            </div>
                            <div class="d-flex flex-column align-items-center">
                                <h4 class="year-number mb-0 w-110-px fw-medium h-110-px bg-white text-black rounded-circle border border-dark d-flex justify-content-center align-items-center">2025</h4>
                                <span class="text-neutral-500 text-md text-uppercase fw-medium mt-8">Year</span>
                            </div>
                        </div>

                        <div class="mt-24 max-w-500-px text-start">
                            <span class="fw-semibold text-neutral-600 text-lg text-hover-neutral-600">Something exciting is always happening! Want to be the first to know? Stay Connected</span>
                            <form action="#" class="mt-16 d-flex gap-16 flex-sm-row flex-column">
                                <input type="email" class="form-control text-start py-24 flex-grow-1" placeholder="info@norloxsolutions.com" required>
                                <button type="submit" class="btn btn-primary-600 px-24 flex-shrink-0 d-flex align-items-center justify-content-center gap-8">
                                    <i class="ri-notification-2-line"></i> Knock Us
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="col-lg-6 d-lg-block d-none">
                        <img src="{{ asset('assets/images/coming-soon/coming-soon.png') }}" alt="">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function() {
            const now = new Date();
            const days = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
            const months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

            document.querySelector(".day-name").textContent = days[now.getDay()];
            document.querySelector(".month-name").textContent = months[now.getMonth()];
            document.querySelector(".date-number").textContent = ("0" + now.getDate()).slice(-2);
            document.querySelector(".year-number").textContent = now.getFullYear();
        })();
    </script>

    <x-script />

</body>

</html>