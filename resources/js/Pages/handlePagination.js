import { ref, computed } from "vue";

export default function handlePagination(data) {
    let page = ref(1);

    const perPage = 30;

    const paginatedData = computed(() =>
        data.slice((page.value - 1) * perPage, page.value * perPage)
    );

    const nextPage = () => {
        if (page.value !== Math.ceil(data.length / perPage)) {
            page.value += 1;
        }
    };

    const backPage = () => {
        if (page.value !== 1) {
            page.value -= 1;
        }
    };

    const goToPage = (numPage) => {
        page.value = numPage;
    };

    return { data, paginatedData, perPage, page, nextPage, backPage, goToPage };
}
